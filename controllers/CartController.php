<?php

namespace app\controllers;

use app\models\CartItems;
use app\models\Carts;
use app\models\ProductVariants;
use app\models\forms\Cart\AddToCartForm;
use app\models\response\Cart\CartResponse;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class CartController extends BaseController
{
    public $modelClass = 'app\models\Carts';
    // public function actionIndex()
    // {
    //     $query = Carts::find();
    //     $data = $this->paginate($query);
    //     return $this->json(true, $data, 'Carts retrieved successfully');
    // }

    public function actionView($id)
    {
        $model = CartResponse::findOne(['id' => $id]);
        if (!$model) {
            return $this->json(false, null, 'Cart not found', 404);
        }
        return $this->json(true, $model, 'Cart retrieved successfully');
    }

    public function actionCreate()
    {
        $model = new AddToCartForm();
        $model->load($this->request->bodyParams, '');

        if ($model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $cart = $this->addItemFromForm($model);
                $transaction->commit();
                return $this->json(true, $cart, 'Cart created successfully', 201);
            } catch (NotFoundHttpException $exception) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                return $this->json(false, null, $exception->getMessage(), 404);
            } catch (\Throwable $exception) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->json(false, null, 'Internal server error', 500);
            }
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        try {
            $model = $this->findModel($id);
            return $this->json(true, $model, 'Use cart item actions to update quantities.');
        } catch (NotFoundHttpException $exception) {
            return $this->json(false, null, $exception->getMessage(), 404);
        }
    }

    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
            if ($model->delete()) {
                return $this->json(true, null, 'Cart deleted successfully');
            }
        } catch (NotFoundHttpException $exception) {
            return $this->json(false, null, $exception->getMessage(), 404);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete cart', 500);
    }

    protected function findModel($id)
    {
        if (($model = Carts::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Cart not found');
    }

    // public function actionAddItem()
    // {
    //     $form = new AddToCartForm();
    //     $form->load($this->request->bodyParams, '');

    //     if (!$form->validate()) {
    //         return $this->json(false, $form->errors, 'Validation failed', 422);
    //     }

    //     $cart = $this->addItemFromForm($form);
    //     return $this->json(true, $cart, 'Cart item added successfully');
    // }

    public function actionAddItems()
    {
        $body = $this->request->bodyParams;
        $userId = (int) ($body['user_id'] ?? 0);
        $items = $body['items'] ?? null;

        if ($userId < 1) {
            return $this->json(false, null, 'user_id is required', 400);
        }
        if (!is_array($items) || empty($items)) {
            return $this->json(false, null, 'items must be a non-empty array', 400);
        }

        $forms = [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                return $this->json(false, null, 'items[' . $index . '] must be an object', 400);
            }

            $form = new AddToCartForm();
            $form->user_id = $userId;
            $form->product_id = $item['product_id'] ?? null;
            $form->product_variant_id = $item['product_variant_id'] ?? null;
            $form->quantity = $item['quantity'] ?? null;

            if (!$form->validate()) {
                return $this->json(false, $form->errors, 'Validation failed', 422);
            }

            $forms[] = $form;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $cart = $this->getOrCreateCartByUserId($userId);
            $this->addItemsFromForms($cart, $forms);
            $this->recalculateTotal($cart);

            $transaction->commit();
            return $this->json(true, $cart, 'Cart items added successfully');
        } catch (NotFoundHttpException $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            return $this->json(false, null, $exception->getMessage(), 404);
        } catch (BadRequestHttpException $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            return $this->json(false, null, $exception->getMessage(), 400);
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, $exception->getMessage()    , 500);
        }
    }

    private function addItemsFromForms(Carts $cart, array $forms): void
    {
        $variantIds = array_values(array_unique(array_map(static function (AddToCartForm $form) {
            return (int) $form->product_variant_id;
        }, $forms)));

        $variants = ProductVariants::find()
            ->where(['id' => $variantIds])
            ->indexBy('id')
            ->all();

        $existingItems = CartItems::find()
            ->where([
                'cart_id' => $cart->id,
                'product_variant_id' => $variantIds,
            ])
            ->indexBy('product_variant_id')
            ->all();

        foreach ($forms as $form) {
            $variant = $variants[(int) $form->product_variant_id] ?? null;
            if ($variant === null || (int) $variant->product_id !== (int) $form->product_id) {
                throw new NotFoundHttpException('Product variant not found');
            }

            $item = $existingItems[(int) $variant->id] ?? null;
            $price = $variant->sale_price !== null ? $variant->sale_price : $variant->price;
            $currentQuantity = $item === null ? 0 : (int) $item->quantity;
            $newQuantity = $currentQuantity + (int) $form->quantity;

            if ($newQuantity > (int) $variant->stock) {
                throw new BadRequestHttpException(
                    'Requested quantity for variant ' . $variant->id . ' exceeds stock. Available stock: ' . $variant->stock
                );
            }

            if ($item === null) {
                $item = new CartItems();
                $item->setAttributes([
                    'cart_id' => $cart->id,
                    'product_id' => $form->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 0,
                ], false);
                $existingItems[(int) $variant->id] = $item;
            }

            $item->setAttributes([
                'quantity' => $newQuantity,
                'price' => $price,
            ], false);

            if (!$item->save()) {
                throw new \RuntimeException('Failed to save cart item: ' . json_encode($item->errors));
            }
        }
    }

    // public function actionUpdateItem($id)
    // {
    //     $body = $this->request->bodyParams;
    //     $userId = (int) ($body['user_id'] ?? 0);
    //     if ($userId < 1) {
    //         return $this->json(false, null, 'user_id is required', 400);
    //     }
    //     $cart = $this->getOrCreateCartByUserId($userId);

    //     $item = CartItems::findOne([
    //         'id' => $id,
    //         'cart_id' => $cart->id,
    //     ]);

    //     if ($item === null) {
    //         return $this->json(false, null, 'Cart item not found', 404);
    //     }

    //     $quantity = (int) ($body['quantity'] ?? 0);
    //     if ($quantity < 1) {
    //         $item->delete();
    //     } else {
    //         $item->quantity = $quantity;
    //         $item->save();
    //     }

    //     $this->recalculateTotal($cart);
    //     return $this->json(true, $cart, 'Cart item updated successfully');
    // }

    // public function actionRemoveItem($id)
    // {
    //     $body = $this->request->bodyParams;
    //     $userId = (int) ($body['user_id'] ?? 0);
    //     if ($userId < 1) {
    //         return $this->json(false, null, 'user_id is required', 400);
    //     }
    //     $cart = $this->getOrCreateCartByUserId($userId);

    //     $item = CartItems::findOne([
    //         'id' => $id,
    //         'cart_id' => $cart->id,
    //     ]);

    //     if ($item === null) {
    //         return $this->json(false, null, 'Cart item not found', 404);
    //     }

    //     $item->delete();
    //     $this->recalculateTotal($cart);
    //     return $this->json(true, $cart, 'Cart item removed successfully');
    // }

    public function actionRemoveItems()
    {
        $body = $this->request->bodyParams;
        if (empty($body)) {
            $body = $this->request->post();
        }
        $userId = (int) ($body['user_id'] ?? 0);
        $itemIds = $body['item_ids'] ?? null;

        if ($userId < 1) {
            return $this->json(false, null, 'user_id is required', 400);
        }
        if (!is_array($itemIds) || empty($itemIds)) {
            return $this->json(false, null, 'item_ids must be a non-empty array', 400);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $cart = Carts::findOne(['user_id' => $userId]);
            if ($cart === null) {
                $transaction->rollBack();
                return $this->json(false, null, 'Cart not found', 404);
            }

            $itemIds = array_values(array_filter(array_map('intval', $itemIds), static fn($id) => $id > 0));
            if (empty($itemIds)) {
                $transaction->rollBack();
                return $this->json(false, null, 'item_ids must contain positive integers', 400);
            }

            $existingItemCount = CartItems::find()
                ->where([
                    'id' => $itemIds,
                    'cart_id' => $cart->id,
                ])
                ->count();
            if ((int) $existingItemCount === 0) {
                $transaction->rollBack();
                return $this->json(false, null, 'Cart items not found for this user', 404);
            }

            $deleted = CartItems::deleteAll([
                'id' => $itemIds,
                'cart_id' => $cart->id,
            ]);

            $this->recalculateTotal($cart);
            $transaction->commit();
            return $this->json(true, ['deleted' => $deleted, 'cart' => $cart], 'Cart items removed successfully');
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }
    }

    public function actionClearCart()
    {
        $body = $this->request->bodyParams;
        $userId = (int) ($body['user_id'] ?? 0);
        if ($userId < 1) {
            return $this->json(false, null, 'user_id is required', 400);
        }

        try {
            $cart = Carts::findOne(['user_id' => $userId]);
            if ($cart === null) {
                return $this->json(false, null, 'Cart not found', 404);
            }

            CartItems::deleteAll(['cart_id' => $cart->id]);
            $cart->setAttributes(['total' => 0], false);
            if (!$cart->save(false, ['total'])) {
                throw new \RuntimeException('Failed to update cart total.');
            }

            return $this->json(true, $cart, 'Cart cleared successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }
    }

    private function getOrCreateCartByUserId(int $userId)
    {
        $cart = Carts::findOne(['user_id' => $userId]);
        if ($cart !== null) {
            return $cart;
        }

        $cart = new Carts();
        $cart->setAttributes([
            'user_id' => $userId,
            'total' => 0,
        ], false);
        if (!$cart->save(false)) {
            throw new \RuntimeException('Failed to create cart.');
        }

        return $cart;
    }

    private function recalculateTotal(Carts $cart)
    {
        $total = (float) CartItems::find()
            ->where(['cart_id' => $cart->id])
            ->sum('price * quantity');

        $cart->setAttributes(['total' => $total], false);
        if (!$cart->save(false, ['total'])) {
            throw new \RuntimeException('Failed to update cart total.');
        }
    }

    private function addItemFromForm(AddToCartForm $form)
    {
        $variant = ProductVariants::findOne(['id' => $form->product_variant_id]);
        if ($variant === null || (int) $variant->product_id !== (int) $form->product_id) {
            throw new NotFoundHttpException('Product variant not found');
        }

        $cart = $this->getOrCreateCartByUserId((int) $form->user_id);

        $item = CartItems::findOne([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);

        $price = $variant->sale_price !== null ? $variant->sale_price : $variant->price;

        if ($item === null) {
            $item = new CartItems();
            $item->setAttributes([
                'cart_id' => $cart->id,
                'product_id' => $form->product_id,
                'product_variant_id' => $variant->id,
                'quantity' => $form->quantity,
                'price' => $price,
            ], false);
        } else {
            $item->setAttributes([
                'quantity' => (int) $item->quantity + (int) $form->quantity,
                'price' => $price,
            ], false);
        }

        if (!$item->save()) {
            throw new \RuntimeException('Failed to save cart item: ' . json_encode($item->errors));
        }
        $this->recalculateTotal($cart);

        return $cart;
    }
}
