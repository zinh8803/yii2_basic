<?php

namespace app\controllers;

use app\models\ProductVariants;
use app\models\search\ProductVariantSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class ProductVariantController extends BaseController
{
    public $modelClass = 'app\\models\\ProductVariants';

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
        $searchModel = new ProductVariantSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Product variants retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Product variant retrieved successfully');
    }
    public function actionCreate()
    {
        $model = new ProductVariants();

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Product variant created successfully', 201);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Product variant updated successfully');
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Product variant deleted successfully');
    }
    protected function findModel($id)
    {
        if (($model = ProductVariants::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
