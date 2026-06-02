<?php

namespace app\controllers;

use app\models\Cart;
use app\models\CartItem;
use app\models\CartItemHandler;
use app\models\forms\Cart\AddToCartForm;
use Yii;
use yii\web\NotFoundHttpException;

class CartController extends BaseController
{
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Cart retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new AddToCartForm();

        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $handler = new CartItemHandler();
            $model = $handler->addItemsToCart(
                (int) $form->user_id,
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

    protected function findModel($id)
    {
        $model = Cart::findOne($id);
        if (!isset($model)) {
            throw new NotFoundHttpException('Cart not found');
        }
        return $model;
    }

    public function actionRemoveItems()
    {
        $body = $this->request->bodyParams;
        if (empty($body)) {
            $body = $this->request->post();
        }
        $userId = (int) ($body['user_id'] ?? 0);
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
        $body = $this->request->bodyParams;
        $userId = (int) ($body['user_id'] ?? 0);
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
