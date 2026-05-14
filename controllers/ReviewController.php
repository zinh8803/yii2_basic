<?php

namespace app\controllers;

use app\models\Reviews;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class ReviewController extends BaseController
{
    public $modelClass = 'app\\models\\Reviews';

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
        $query = Reviews::find();
        $data = $this->paginate($query);
        return $this->json(true, $data, 'Reviews retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Review retrieved successfully');
    }

    public function actionCreate()
    {
        $model = new Reviews();

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Review created successfully', 201);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Review updated successfully');
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Review deleted successfully');
    }

    protected function findModel($id)
    {
        if (($model = Reviews::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
