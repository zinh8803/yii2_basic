<?php

namespace app\controllers;

use app\models\CartItems;
use app\models\Carts;
use app\models\ProductVariants;
use app\models\forms\Cart\AddToCartForm;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;


class CartController extends BaseController
{
    public $modelClass = 'app\\models\\Carts';

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'add-item' => ['POST'],
                        'delete' => ['POST'],
                        'remove-item' => ['POST'],
                        'update-item' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex()
    {
        $query = Carts::find();
        $data = $this->paginate($query);
        return $this->json(true, $data, 'Carts retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Cart retrieved successfully');
    }

    public function actionCreate()
    {
        $model = new AddToCartForm();
        $model->load($this->request->bodyParams, '');

        if ($model->validate()) {
            $cart = $this->addItemFromForm($model);
            return $this->json(true, $cart, 'Cart created successfully', 201);
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
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Cart deleted successfully');
    }

    protected function findModel($id)
    {
        if (($model = Carts::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAddItem()
    {
        $form = new AddToCartForm();
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $cart = $this->addItemFromForm($form);
        return $this->json(true, $cart, 'Cart item added successfully');
    }

    public function actionUpdateItem($id)
    {
        $body = $this->request->bodyParams;
        $userId = (int) ($body['user_id'] ?? 0);
        if ($userId < 1) {
            return $this->json(false, null, 'user_id is required', 400);
        }
        $cart = $this->getOrCreateCartByUserId($userId);

        $item = CartItems::findOne([
            'id' => $id,
            'cart_id' => $cart->id,
        ]);

        if ($item === null) {
            throw new NotFoundHttpException('Cart item not found.');
        }

        $quantity = (int) ($body['quantity'] ?? 0);
        if ($quantity < 1) {
            $item->delete();
        } else {
            $item->quantity = $quantity;
            $item->save();
        }

        $this->recalculateTotal($cart);
        return $this->json(true, $cart, 'Cart item updated successfully');
    }

    public function actionRemoveItem($id)
    {
        $body = $this->request->bodyParams;
        $userId = (int) ($body['user_id'] ?? 0);
        if ($userId < 1) {
            return $this->json(false, null, 'user_id is required', 400);
        }
        $cart = $this->getOrCreateCartByUserId($userId);

        $item = CartItems::findOne([
            'id' => $id,
            'cart_id' => $cart->id,
        ]);

        if ($item === null) {
            throw new NotFoundHttpException('Cart item not found.');
        }

        $item->delete();
        $this->recalculateTotal($cart);
        return $this->json(true, $cart, 'Cart item removed successfully');
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
            throw new NotFoundHttpException('Product variant not found.');
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
