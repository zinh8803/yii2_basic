<?php

namespace app\controllers;

use app\models\forms\Review\CreateReviewForm;
use app\models\forms\Review\UpdateApprovedReviewForm;
use app\models\OrderItems;
use app\models\Products;
use app\models\Reviews;
use app\models\search\ReviewSearch;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class ReviewController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new ReviewSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Reviews retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Review retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new CreateReviewForm();
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $hasPurchased = OrderItems::find()
            ->joinWith('order')
            ->where([
                'orders.user_id' => $form->user_id,
                'order_items.product_id' => $form->product_id,
            ])
            ->exists();

        if (!$hasPurchased) {
            return $this->json(false, null, 'You must purchase this product before reviewing.', 403);
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $model = new Reviews();
            $model->setAttributes($form->attributes, false);

            if (!$model->save()) {
                $transaction->rollBack();
                return $this->json(false, $model->errors, 'Validation failed', 422);
            }

            $stats = Reviews::find()
                ->select([
                    'rating_avg' => 'AVG(rating)',
                    'rating_count' => 'COUNT(*)',
                ])
                ->where(['product_id' => $form->product_id])
                ->asArray()
                ->one();

            Products::updateAll([
                'rating_avg' => round((float) $stats['rating_avg'], 1),
                'rating_count' => (int) $stats['rating_count'],
            ], [
                'id' => $form->product_id,
            ]);

            $transaction->commit();

            return $this->json(true, $model, 'Review created successfully', 201);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, $exception->getMessage() , 500);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');
        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Review updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionChangeApprove($id)
    {
        $model = Reviews::findOne(['id' => $id]);
        if (!$model) {
            return $this->json(false, null, 'Review not found', 404);
        }
        $form = new UpdateApprovedReviewForm();
        $form->id = $id;
        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }
        $model->is_approved = (int) !$model->is_approved;
        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Review approval status updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
            if ($model->delete()) {
                return $this->json(true, null, 'Review deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete review', 500);
    }

    protected function findModel($id)
    {
        if (($model = Reviews::findOne(['id' => $id])) !== null) {
            return $model;
        }

        return $this->json(false, null, 'Review not found', 404);
    }
}
