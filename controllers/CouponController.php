<?php

namespace app\controllers;

use app\models\Coupons;
use app\models\forms\Coupon\CreateCouponForm;
use app\models\forms\Coupon\UpdateCouponForm;
use app\models\response\Coupon\CouponResponse;
use Yii;

class CouponController extends BaseController
{
    public $modelClass = 'app\models\Coupons';
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

    public function actionIndex()
    {
        $query = CouponResponse::find();
        $data = $this->paginate($query);
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
            $coupon->code = $form->code;
            $coupon->type = $form->type;
            $coupon->value = $form->value;
            $coupon->min_order_value = $form->min_order_value;
            $coupon->max_discount = $form->max_discount;
            $coupon->max_usage = $form->max_usage;
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
            $model->code = $form->code;
            $model->type = $form->type;
            $model->value = $form->value;
            $model->min_order_value = $form->min_order_value;
            $model->max_discount = $form->max_discount;
            $model->max_usage = $form->max_usage;
            $model->starts_at = strtotime($form->starts_at);
            $model->expires_at = strtotime($form->expires_at);
            $model->is_active = $form->is_active;

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


}
