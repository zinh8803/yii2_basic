<?php

namespace app\controllers;

use app\models\Cart;
use app\models\CartItem;
use app\models\CartItemHandler;
use app\models\forms\Cart\AddToCartForm;
use Yii;
use yii\filters\auth\HttpBearerAuth;

class CartController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            //   'except' => ['index', 'view'],
        ];

        return $behaviors;
    }

    public function actionDetail()
    {
        $this->checkPermission('cart.view');
        $model = $this->findModel();
        return $this->formatJson(true, $model, 'Cart retrieved successfully');
    }

    public function actionCreate()
    {
        $this->checkPermission('cart.create');
        $form = new AddToCartForm();
        $user_id = Yii::$app->user->id;
        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $handler = new CartItemHandler();
            $model = $handler->addItemsToCart(
                $user_id,
                $form->getItems()
            );
            $transaction->commit();
            return $this->formatJson(true, $model, 'Cart items added successfully', 201);

        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, $exception->getMessage(), 'Internal server error', 500);
        }
    }

    protected function findModel()
    {
        $userId = Yii::$app->user->id;
        $model = Cart::find()
            ->where(['user_id' => $userId])->one();
        if (!isset($model)) {
            $model = new Cart();
            $model->user_id = $userId;
            $model->total = 0;
            $model->save(false);
        }
        return $model;
    }

    public function actionRemoveItems()
    {
        $this->checkPermission('cart.removeItems');
        $body = $this->request->bodyParams;
        if (empty($body)) {
            $body = $this->request->post();
        }
        $userId = Yii::$app->user->id;
        $itemIds = $body['item_ids'] ?? ($body['item_id'] ?? null);

        if ($userId < 1) {
            return $this->formatJson(false, null, 'user_id is required', 400);
        }
        if ($itemIds === null || $itemIds === '' || (is_array($itemIds) && empty($itemIds))) {
            return $this->formatJson(false, null, 'item_id or item_ids is required', 400);
        }
        $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $handler = new CartItemHandler();
            $data = $handler->removeItemsFromCart($userId, $itemIds);
            $transaction->commit();
            return $this->formatJson(true, $data, 'Cart items removed successfully');
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, $exception->getMessage(), $exception->getMessage(), 404);
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', 500);
        }
    }

    public function actionClearCart()
    {
        $this->checkPermission('cart.clearCart');
        $body = $this->request->bodyParams;
        $userId = Yii::$app->user->id;
        if ($userId < 1) {
            return $this->formatJson(false, null, 'user_id is required', 400);
        }

        try {
            $cart = Cart::findOne(['user_id' => $userId]);
            if ($cart === null) {
                return $this->formatJson(false, null, 'Cart not found', 404);
            }

            CartItem::deleteAll(['cart_id' => $cart->id]);
            $cart->setAttributes(['total' => 0], false);
            if (!$cart->save(false, ['total'])) {
                throw new \RuntimeException('Failed to update cart total.');
            }

            return $this->formatJson(true, $cart, 'Cart cleared successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', 500);
        }
    }

}
