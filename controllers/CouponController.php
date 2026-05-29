<?php

namespace app\controllers;

use app\models\Coupons;
use app\models\forms\Coupon\CreateCouponForm;
use app\models\forms\Coupon\UpdateCouponForm;
use app\models\response\Coupon\CouponResponse;
use app\models\search\CouponSearch;
use Yii;

class CouponController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new CouponSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Coupons retrieved successfully');
    }

    public function actionView($id)
    {
        $model = CouponResponse::findOne(['id' => $id]);
        if ($model) {
            return $this->json(true, $model, 'Coupon retrieved successfully');
        }
        return $this->json(false, null, 'Coupon not found', 404);
    }

    public function actionCreate()
    {
        $form = new CreateCouponForm();
        $form->load($this->request->bodyParams, '');

        if ($form->validate()) {
            $coupon = new Coupons();

            $coupon->setAttributes($form->getAttributes([
                'code',
                'type',
                'value',
                'min_order_value',
                'max_discount',
                'max_usage',
            ]), false);

            $coupon->used_count = 0;
            $coupon->starts_at = strtotime($form->starts_at);
            $coupon->expires_at = strtotime($form->expires_at);

            try {
                if ($coupon->save()) {
                    return $this->json(true, $coupon, 'Coupon created successfully', 201);
                }

                Yii::error('Failed to save coupon: ' . json_encode($coupon->errors));
                foreach ($coupon->getErrors() as $attribute => $messages) {
                    foreach ($messages as $message) {
                        $form->addError($attribute, $message);
                    }
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->json(false, null, 'Internal server error', 500);
            }
        }

        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = CouponResponse::findOne(['id' => $id]);
        if (!$model) {
            return $this->json(false, null, 'Coupon not found', 404);
        }
        $form = new UpdateCouponForm();
        $form->id = $id;
        $data = $this->request->bodyParams;
        if (empty($data)) {
            $data = $this->request->post();
        }
        $form->load($data, '');

        if ($form->validate()) {
            $model->setAttributes($form->getAttributes([
                'code',
                'type',
                'value',
                'min_order_value',
                'max_discount',
                'max_usage',
                'is_active',
            ]), false);
            $model->starts_at = strtotime($form->starts_at);
            $model->expires_at = strtotime($form->expires_at);

            try {
                if ($model->save()) {
                    return $this->json(true, $model, 'Coupon updated successfully');
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->json(false, null, 'Internal server error', 500);
            }
        }

        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $model = CouponResponse::findOne(['id' => $id]);
        if (!$model) {
            return $this->json(false, null, 'Coupon not found', 404);
        }
        try {
            if ($model->delete()) {
                return $this->json(true, null, 'Coupon deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete coupon', 500);
    }

    public function actionCheckValid($code)
    {
        $model = CouponResponse::findOne(['code' => $code, 'is_active' => 1]);
        if (!$model) {
            return $this->json(false, null, 'Invalid coupon code', 404);
        }
        if ($model->used_count >= $model->max_usage) {
            return $this->json(false, null, 'Coupon usage limit reached', 422);
        }
        $currentTime = time();
        if ($model->starts_at > $currentTime) {
            return $this->json(false, null, 'Coupon not active yet', 422);
        }
        if ($model->expires_at < $currentTime) {
            return $this->json(false, null, 'Coupon has expired', 422);
        }
        return $this->json(true, $model, 'Coupon is valid');
    }
}
