<?php

namespace app\controllers;

use app\models\Brands;
use app\models\forms\Brand\CreateBrandForm;
use app\models\forms\Brand\UpdateBrandForm;
use app\models\response\Brand\BrandResponse;
use app\models\search\BrandSearch;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class BrandController extends BaseController
{
    public $modelClass = 'app\models\Brands';
    public function actionIndex()
    {
        $searchModel = new BrandSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, '', false);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Brands retrieved successfully');
    }

    public function actionView($id)
    {
        $query = BrandResponse::find()->where(['id' => $id])->active();
        $model = $query->one();
        if (!$model) {
            return $this->json(false, null, 'Brand not found', 404);
        }
        return $this->json(true, $model, 'Brand retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new CreateBrandForm();
        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }
        $model = new Brands();
        $model->setAttributes($form->attributes, false);
        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Brand created successfully', 201);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = Brands::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Brand not found', 404);
        }

        $form = new UpdateBrandForm();
        $form->id = $id;
        $data = $this->request->bodyParams;
        if (empty($data)) {
            $data = $this->request->post();
        }
        $form->load($data, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        try {
            $model->setAttributes($form->attributes, false);

            if ($model->save()) {
                return $this->json(true, $model, 'Brand updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $model = Brands::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Brand not found', 404);
        }
        try {
            if ($model->delete()) {
                return $this->json(true, null, 'Brand deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete brand', 500);
    }
}
