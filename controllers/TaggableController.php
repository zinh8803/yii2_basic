<?php

namespace app\controllers;

use app\models\Taggables;
use Yii;
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
        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Taggable created successfully', 201);
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
                return $this->json(true, $model, 'Taggable updated successfully');
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
                return $this->json(true, null, 'Taggable deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete taggable', 500);
    }

    protected function findModel($id)
    {
        if (($model = Taggables::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
