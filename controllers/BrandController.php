<?php

namespace app\controllers;

use app\models\Brand;
use app\models\forms\brand\BrandForm;
use app\models\search\BrandSearch;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class BrandController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['index', 'view'],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $searchModel = new BrandSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, '', true);
        return $this->successPaginate($dataProvider, 'Brands retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Brand retrieved successfully');
    }

    public function actionCreate()
    {
        //     $this->checkPermission('brand.create');
        $form = new BrandForm([
            'scenario' => BrandForm::SCENARIO_CREATE,
        ]);
        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', self::HTTP_BAD_REQUEST);
        }
        $model = new Brand();
        $model->setAttributes($form->attributes, false);
        try {
            if ($model->save(false)) {
                return $this->formatJson(true, $model, 'Brand created successfully', self::HTTP_CREATED);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionUpdate($id)
    {
        $this->checkPermission('brand.update');
        $model = $this->findModel($id);
        $form = new BrandForm([
            'scenario' => BrandForm::SCENARIO_UPDATE,
        ]);
        $form->id = $id;
        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', self::HTTP_BAD_REQUEST);
        }
        try {
            $model->setAttributes($form->getAttributes(['name', 'status']), false);
            if ($model->save(false)) {
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
        $this->checkPermission('brand.softDelete');
        $model = $this->findModel($id);
        try {
            if ($model->softDelete()) {
                return $this->formatJson(true, null, 'Brand deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, $exception->getMessage(), 'Internal server error', self::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->formatJson(false, null, 'Failed to delete brand', self::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function actionTrash()
    {
        $this->checkPermission('brand.viewTrash');
        $searchModel = new BrandSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, '', null, 'trash');
        return $this->successPaginate($dataProvider, 'Brands retrieved successfully');
    }


    public function actionRestore($id)
    {
        $this->checkPermission('brand.restore');
        $model = $this->findModel($id);
        try {
            if ($model->restore()) {
                return $this->formatJson(true, null, 'Brand restored successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', self::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->formatJson(false, null, 'Failed to restore brand', self::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function actionForceDelete($id)
    {
        $this->checkPermission('brand.forceDelete');
        $model = $this->findModel($id);
        try {
            if ($model->delete()) {
                return $this->formatJson(true, null, 'Brand permanently deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', self::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->formatJson(false, null, 'Failed to permanently delete brand', self::HTTP_INTERNAL_SERVER_ERROR);
    }


    public function findModel($id)
    {
        $model = BrandForm::find()
            ->where(['id' => $id])
            ->one();
        if (!isset($model)) {
            throw new NotFoundHttpException('Brand not found');
        }
        return $model;
    }
}
