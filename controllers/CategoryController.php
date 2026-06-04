<?php

namespace app\controllers;

use app\models\Categories;
use app\models\Category;
use app\models\forms\category\CategoryForm;
use app\models\search\CategorySearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class CategoryController extends BaseController
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
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search($this->request->queryParams, true);
        return $this->successPaginate($dataProvider, 'Categories retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Category retrieved successfully');
    }

    public function actionCreate()
    {
        $this->checkPermission('category.create');
        $form = new CategoryForm([
            'scenario' => CategoryForm::SCENARIO_CREATE,
        ]);
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $model = new Category();
        $model->setAttributes($form->attributes, false);

        try {
            if ($model->save(false)) {
                return $this->formatJson(true, $model, 'Category created successfully', 201);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionUpdate($id)
    {
        $this->checkPermission('category.update');
        $model = $this->findModel($id);
        $form = new CategoryForm([
            'scenario' => CategoryForm::SCENARIO_UPDATE,
        ]);
        $form->id = $id;
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }
        $model->setAttributes($form->getAttributes(['name', 'parent_id', 'status']), false);
        try {
            if ($model->save(false)) {
                return $this->formatJson(true, $model, 'Category updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }

        return $this->formatJson(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $this->checkPermission('category.softDelete');
        $model = $this->findModel($id);
        if ($model->hasChildren()) {
            return $this->formatJson(false, null, 'Cannot delete category with active subcategories', 400);
        }
        try {
            if ($model->softDelete()) {
                return $this->formatJson(true, null, 'Category deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    public function actionTrash()
    {
        $this->checkPermission('category.viewTrash');
        $query = Category::find()
            ->deleted()
            ->orderBy(['id' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => (int) Yii::$app->request->get('limit', 10),
                'page' => max(0, (int) Yii::$app->request->get('page', 1) - 1),
            ],
        ]);
        return $this->successPaginate($dataProvider, 'Categories retrieved successfully');
    }

    public function actionRestore($id)
    {
        $this->checkPermission('category.restore');
        $model = $this->findModel($id);
        try {
            if ($model->restore()) {
                return $this->formatJson(true, null, 'category restored successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', self::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->formatJson(false, null, 'Failed to restore category', self::HTTP_INTERNAL_SERVER_ERROR);

    }

    public function actionForceDelete($id)
    {
        $this->checkPermission('category.forceDelete');
        $model = $this->findModel($id);
        if ($model->hasChildren()) {
            return $this->formatJson(false, null, 'Cannot delete category with active subcategories', 400);
        }
        try {
            if ($model->delete()) {
                return $this->formatJson(true, null, 'Category deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    public function findModel($id, $tree = true)
    {
        $model = CategoryForm::find()
            ->where(['id' => $id])
            //    ->tree()
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('Category not found');
        }
        return $model;
    }

}
