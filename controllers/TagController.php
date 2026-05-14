<?php

namespace app\controllers;

use app\models\Tags;
use app\models\search\TagSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class TagController extends BaseController
{
    public $modelClass = 'app\\models\\Tags';

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
        $searchModel = new TagSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Tags retrieved successfully');
    }
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Tag retrieved successfully');
    }
    public function actionCreate()
    {
        $model = new Tags();

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Tag created successfully', 201);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Tag updated successfully');
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Tag deleted successfully');
    }
    protected function findModel($id)
    {
        if (($model = Tags::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
