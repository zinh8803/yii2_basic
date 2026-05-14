<?php

namespace app\controllers;

use app\models\Coupons;
use app\models\forms\Coupon\CreateCouponForm;
use app\models\search\CouponSearch;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class CouponController extends BaseController
{
    public $modelClass = 'app\\models\\Coupons';

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
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }
    public function actionIndex()
    {
        $searchModel = new CouponSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Coupons retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Coupon retrieved successfully');
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
            $coupon->is_active = 1;

            if ($coupon->save()) {
                return $this->json(true, $coupon, 'Coupon created successfully', 201);
            }

            Yii::error('Failed to save coupon: ' . json_encode($coupon->errors));
            foreach ($coupon->getErrors() as $attribute => $messages) {
                foreach ($messages as $message) {
                    $form->addError($attribute, $message);
                }
            }
        }

        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Coupon updated successfully');
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Coupon deleted successfully');
    }

    protected function findModel($id)
    {
        if (($model = Coupons::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
