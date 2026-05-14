<?php

namespace app\controllers;

use app\models\Brands;
use app\models\forms\Brand\CreateBrandForm;
use app\models\forms\Brand\UpdateBrandForm;
use app\models\response\Brand\BrandResponse;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class BrandController extends BaseController
{
    public $modelClass = 'app\models\Brands';

    protected function verbs()
    {
        return [
            'index' => ['GET'],
            'view' => ['GET'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    public function actionIndex()
    {
        $query = BrandResponse::find();
        $data = $this->paginate($query);
        return $this->json(true, $data, 'Brands retrieved successfully');
    }

    public function actionView($id)
    {
        $query = BrandResponse::find()->where(['id' => $id]);
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
        $model->name = $form->name;
        $model->slug = $form->slug;
        $model->status = $form->status;
        if ($model->save()) {
            return $this->json(true, $model, 'Brand created successfully', 201);
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
        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $model->name = $form->name;
        $model->slug = $form->slug;
        $model->status = $form->status;

        if ($model->save()) {
            return $this->json(true, $model, 'Brand updated successfully');
        }
        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $model = Brands::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Brand not found', 404);
        }
        $model->delete();
        return $this->json(true, null, 'Brand deleted successfully');
    }

}
