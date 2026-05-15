<?php

namespace app\controllers;

use app\models\forms\PostProduct\CreatePostProductForm;
use app\models\PostProducts;
use app\models\response\PostProduct\PostProductResponse;
use Yii;

class PostProductController extends BaseController
{
    public $modelClass = 'app\models\PostProducts';

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
        $query = PostProductResponse::find();
        $data = $this->paginate($query);
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
            $model->post_id = $form->post_id;
            $model->product_id = $form->product_id;
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
        $model->post_id = $form->post_id;
        $model->product_id = $form->product_id;
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
