<?php

namespace app\controllers;

use app\models\CartItems;
use app\models\Carts;
use app\models\ProductVariants;
use app\models\forms\Cart\AddToCartForm;
use app\models\response\Cart\CartResponse;
use Yii;

class CartController extends BaseController
{
    public $modelClass = 'app\models\Carts';

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }


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
            try {
                $cart = $this->addItemFromForm($model);
                return $this->json(true, $cart, 'Cart created successfully', 201);
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->json(false, null, 'Internal server error', 500);
            }
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Use cart item actions to update quantities.');
    }

    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
            if ($model->delete()) {
                return $this->json(true, null, 'Cart deleted successfully');
            }
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

        return $this->json(false, null, 'Cart not found', 404);
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

        try {
            $cart = null;
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

                $cart = $this->addItemFromForm($form);
            }

            return $this->json(true, $cart, 'Cart items added successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
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
        $userId = (int) ($body['user_id'] ?? 0);
        $itemIds = $body['item_ids'] ?? null;

        if ($userId < 1) {
            return $this->json(false, null, 'user_id is required', 400);
        }
        if (!is_array($itemIds) || empty($itemIds)) {
            return $this->json(false, null, 'item_ids must be a non-empty array', 400);
        }

        try {
            $cart = $this->getOrCreateCartByUserId($userId);
            $deleted = 0;

            foreach ($itemIds as $itemId) {
                $itemId = (int) $itemId;
                if ($itemId < 1) {
                    continue;
                }

                $item = CartItems::findOne([
                    'id' => $itemId,
                    'cart_id' => $cart->id,
                ]);

                if ($item !== null) {
                    $item->delete();
                    $deleted++;
                }
            }

            $this->recalculateTotal($cart);
            return $this->json(true, ['deleted' => $deleted, 'cart' => $cart], 'Cart items removed successfully');
        } catch (\Throwable $exception) {
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
            $cart->total = 0;
            $cart->save(false, ['total']);

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
        $cart->user_id = $userId;
        $cart->total = 0;
        $cart->save(false);

        return $cart;
    }

    private function recalculateTotal(Carts $cart)
    {
        $total = 0;

        foreach ($cart->cartItems as $item) {
            $total += $item->price * $item->quantity;
        }

        $cart->total = $total;
        $cart->save(false, ['total']);
    }

    private function addItemFromForm(AddToCartForm $form)
    {
        $variant = ProductVariants::findOne(['id' => $form->product_variant_id]);
        if ($variant === null || (int) $variant->product_id !== (int) $form->product_id) {
            return $this->json(false, null, 'Product variant not found', 404);
        }

        $cart = $this->getOrCreateCartByUserId((int) $form->user_id);

        $item = CartItems::findOne([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);

        $price = $variant->sale_price !== null ? $variant->sale_price : $variant->price;

        if ($item === null) {
            $item = new CartItems();
            $item->cart_id = $cart->id;
            $item->product_id = $form->product_id;
            $item->product_variant_id = $variant->id;
            $item->quantity = $form->quantity;
            $item->price = $price;
        } else {
            $item->quantity += $form->quantity;
            $item->price = $price;
        }

        $item->save();
        $this->recalculateTotal($cart);

        return $cart;
    }
}
