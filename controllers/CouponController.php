<?php

namespace app\controllers;

use app\models\Coupon;
use app\models\Coupons;
use app\models\forms\Coupon\CouponForm;
use app\models\response\Coupon\CouponResponse;
use app\models\search\CouponSearch;
use Yii;
use yii\web\NotFoundHttpException;

class CouponController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new CouponSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->formatJson(true, $dataProvider, 'Coupons retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Coupons retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new CouponForm([
            'scenario' => CouponForm::SCENARIO_CREATE,
        ]);
        $form->load($this->request->bodyParams, '');
        if ($form->validate()) {
            $coupon = new Coupon();
            $coupon->setAttributes($form->attributes, false);
            try {
                if ($coupon->save(false)) {
                    return $this->formatJson(true, $coupon, 'Coupon created successfully', self::HTTP_CREATED);
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->formatJson(false, $exception->getMessage(), 'Internal server error', 500);
            }
        }
        return $this->formatJson(false, $form->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new CouponForm([
            'scenario' => CouponForm::SCENARIO_UPDATE,
        ]);
        $form->id = $id;
        $form->load($this->request->bodyParams, '');

        if ($form->validate()) {
            $model->setAttributes($form->getAttributes([
                'code',
                'type',
                'value',
                'min_order_value',
                'max_discount',
                'max_usage',
                'is_active',
                'starts_at',
                'expires_at',
            ]), false);

            try {
                if ($model->save(false)) {
                    return $this->formatJson(true, $model, 'Coupon updated successfully');
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->formatJson(false, $exception->getMessage(), 'Internal server error', 500);
            }
        }

        return $this->formatJson(false, $form->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            if ($model->delete()) {
                return $this->formatJson(true, null, 'Coupon deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', 500);
        }

        return $this->formatJson(false, null, 'Failed to delete coupon', 500);
    }

    public function actionCheckValid($code)
    {
        $model = CouponForm::findOne(['code' => $code, 'is_active' => 1]);
        if (!$model) {
            return $this->formatJson(false, null, 'Invalid coupon code', 404);
        }
        if ($model->used_count >= $model->max_usage) {
            return $this->formatJson(false, null, 'Coupon usage limit reached', 422);
        }
        $currentTime = time();
        if ($model->starts_at > $currentTime) {
            return $this->formatJson(false, null, 'Coupon not active yet', 422);
        }
        if ($model->expires_at < $currentTime) {
            return $this->formatJson(false, null, 'Coupon has expired', 422);
        }
        return $this->formatJson(true, $model, 'Coupon is valid');
    }

    public function findModel($id)
    {
        $model = CouponForm::find()
            ->where(['id' => $id])
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('Coupon not found');
        }
        return $model;
    }
}
