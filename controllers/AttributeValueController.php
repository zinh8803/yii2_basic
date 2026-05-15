<?php

namespace app\controllers;

use app\models\AttributeValues;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class AttributeValueController extends BaseController
{
    public $modelClass = 'app\\models\\AttributeValues';

    public function actionIndex()
    {
        $query = AttributeValues::find();
        $data = $this->paginate($query);
        return $this->json(true, $data, 'Attribute values retrieved successfully');
    }


    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Attribute value retrieved successfully');
    }


    public function actionCreate()
    {
        $model = new AttributeValues();

        $model->load($this->request->bodyParams, '');
        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Attribute value created successfully', 201);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }


    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');
        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Attribute value updated successfully');
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
                return $this->json(true, null, 'Attribute value deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete attribute value', 500);
    }


    protected function findModel($id)
    {
        if (($model = AttributeValues::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
