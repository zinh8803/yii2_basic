<?php

namespace app\controllers;

use app\models\Taggables;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class TaggableController extends BaseController
{
    public $modelClass = 'app\\models\\Taggables';

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
        $query = Taggables::find();
        $data = $this->paginate($query);
        return $this->json(true, $data, 'Taggables retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Taggable retrieved successfully');
    }

    public function actionCreate()
    {
        $model = new Taggables();

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Taggable created successfully', 201);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Taggable updated successfully');
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Taggable deleted successfully');
    }

    protected function findModel($id)
    {
        if (($model = Taggables::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
