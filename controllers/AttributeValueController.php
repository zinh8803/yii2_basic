<?php

namespace app\controllers;

use app\models\AttributeValues;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class AttributeValueController extends BaseController
{
    public $modelClass = 'app\\models\\AttributeValues';

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

        if ($model->save()) {
            return $this->json(true, $model, 'Attribute value created successfully', 201);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }


    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Attribute value updated successfully');
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }


    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Attribute value deleted successfully');
    }


    protected function findModel($id)
    {
        if (($model = AttributeValues::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
