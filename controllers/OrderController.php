<?php

namespace app\controllers;

use app\models\forms\Order\CreateOrderForm;
use app\models\forms\Order\UpdateStatusOrderForm;
use app\models\Order;
use app\models\OrderCreateModel;
use app\models\Payment;
use app\models\search\OrderSearch;
use app\models\User;
use Yii;
use yii\web\NotFoundHttpException;

class OrderController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->formatJson(true, $dataProvider, "Order List");
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Order retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new CreateOrderForm();
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $orderCreateModel = new OrderCreateModel();
            $order = $orderCreateModel->create($form);
            if ($order === null) {
                $transaction->rollBack();
                return $this->formatJson(false, $form->errors, 'Validation failed', 422);
            }

            $transaction->commit();
            return $this->formatJson(true, $order, 'Order created successfully', 201);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, $exception->getMessage(), 500);
        }
    }

    public function actionUpdateStatusOrder($id = null)
    {
        $form = new UpdateStatusOrderForm();
        $form->load($this->request->bodyParams, '');

        $resolvedId = $id ?? $form->id;

        if ($resolvedId === null) {
            return $this->formatJson(false, null, 'Order id is required', 422);
        }

        $model = Order::findOne(['id' => $resolvedId]);

        if (!$model) {
            return $this->formatJson(false, null, 'Order not found', 404);
        }

        $form->id = $resolvedId;

        if ($form->status === null || $form->status === '') {
            $form->status = $model->status;
        }

        if ($form->payment_status === null || $form->payment_status === '') {
            $form->payment_status = $model->payment_status;
        }

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $model->setAttributes([
                'status' => $form->status,
                'payment_status' => $form->payment_status,
            ], false);

            if (!$model->save()) {
                $transaction->rollBack();
                return $this->formatJson(false, $model->errors, 'Validation failed', 422);
            }

            Payment::updateAll(
                [
                    'status' => $form->payment_status,
                    'payment_status' => $form->payment_status,
                ],
                ['order_id' => $model->id]
            );

            $transaction->commit();

            return $this->formatJson(true, $model, 'Order status updated successfully');
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, 'Internal server error', 500);
        }
    }

    protected function findModel($id)
    {
        $model = Order::findOne(['id' => $id]);

        if (!isset($model)) {
            throw new NotFoundHttpException('Order not found');
        }
        return $model;
    }

    public function actionMyOrders($userId)
    {
        $orders = Order::find()
            ->with(['orderItems', 'payments'])
            ->where(['user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
        $exists = User::find()->where(['id' => $userId])->exists();
        if (!$exists) {
            return $this->formatJson(false, null, 'User not found', 404);
        } else {
            return $this->formatJson(true, $orders, 'User orders retrieved successfully');
        }
    }
}
