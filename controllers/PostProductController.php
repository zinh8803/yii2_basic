<?php

namespace app\controllers;

use app\models\PostProducts;
use app\models\search\PostProductSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;


class PostProductController extends BaseController
{
    public $modelClass = 'app\\models\\PostProducts';

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
        $searchModel = new PostProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Post products retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Post product retrieved successfully');
    }

    public function actionCreate()
    {
        $model = new PostProducts();

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Post product created successfully', 201);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Post product updated successfully');
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Post product deleted successfully');
    }
    protected function findModel($id)
    {
        if (($model = PostProducts::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
