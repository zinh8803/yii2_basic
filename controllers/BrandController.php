<?php

namespace app\controllers;

use app\models\Brand;
use app\models\forms\brand\BrandForm;
use app\models\forms\Brand\UpdateBrandForm;
use app\models\response\BrandResponse;
use app\models\search\BrandSearch;
use Yii;
use yii\web\NotFoundHttpException;

class BrandController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new BrandSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, '', false);
        return $this->successPaginate($dataProvider, 'Brands retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Brand retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new BrandForm();
        $form->scenario = BrandForm::SCENARIO_CREATE;

        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', self::HTTP_BAD_REQUEST);
        }
        $model = new Brand();
        $model->setAttributes($form->attributes, false);
        try {
            if ($model->save()) {
                return $this->formatJson(true, $model, 'Brand created successfully', self::HTTP_CREATED);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new BrandForm();
        $form->id = $id;
        $data = $this->request->bodyParams;
        if (empty($data)) {
            $data = $this->request->post();
        }
        $form->scenario = BrandForm::SCENARIO_UPDATE;
        $form->load($data, '');
        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', self::HTTP_BAD_REQUEST);
        }
        try {
            $model->setAttributes($form->attributes, false);
            if ($model->save()) {
                return $this->formatJson(true, $model, 'Brand updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }

        return $this->formatJson(false, null, 'Failed to update brand', self::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            if ($model->delete()) {
                return $this->formatJson(true, null, 'Brand deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', self::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->formatJson(false, null, 'Failed to delete brand', self::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function findModel($id)
    {
        $model = BrandForm::findOne($id);
        if (!isset($model)) {
            throw new NotFoundHttpException('Brand not found');
        }
        return $model;
    }
}
