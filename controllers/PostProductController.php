<?php

namespace app\controllers;

use app\models\forms\PostProduct\CreatePostProductForm;
use app\models\PostProducts;
use app\models\response\PostProduct\PostProductResponse;
use app\models\search\PostProductSearch;
use Yii;

class PostProductController extends BaseController
{
    public $modelClass = 'app\models\PostProducts';
    public function actionIndex()
    {
        $searchModel = new PostProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Post products retrieved successfully');
    }

    public function actionView($id)
    {
        $model = PostProductResponse::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Post product not found', 404);
        }
        return $this->json(true, $model, 'Post product retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new CreatePostProductForm();
        $form->load($this->request->bodyParams, '');

        if ($form->validate()) {
            $model = new PostProducts();
            $model->setAttributes($form->attributes, false);
            try {
                if ($model->save()) {
                    return $this->json(true, $model, 'Post product created successfully', 201);
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->json(false, null, 'Internal server error', 500);
            }

            return $this->json(false, $model->errors, 'Validation failed', 422);
        }
        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = PostProductResponse::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Post product not found', 404);
        }
        $form = new CreatePostProductForm();
        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }
        $model->setAttributes($form->attributes, false);
        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Post product updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $model = PostProductResponse::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Post product not found', 404);
        }
        try {
            if ($model->delete()) {
                return $this->json(true, null, 'Post product deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete post product', 500);
    }
}
