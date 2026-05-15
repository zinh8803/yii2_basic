<?php

namespace app\controllers;

use app\models\Categories;
use app\models\forms\Category\CreateCategoryForm;
use app\models\forms\Category\UpdateCategoryForm;
use app\models\response\Category\CategoryResponse;
use Yii;

class CategoryController extends BaseController
{
    public $modelClass = 'app\models\Categories';

    /**
     * @inheritDoc
     */
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
        $query = CategoryResponse::find()
            ->where(['parent_id' => null])
            ->with(['children']);
        $data = $this->paginate($query);
        return $this->json(true, $data, 'Categories retrieved successfully');
    }

    public function actionView($id)
    {
        $query = CategoryResponse::find()
            ->where(['id' => $id])
            ->with(['children']);
        $model = $query->one();
        if (!$model) {
            return $this->json(false, null, 'Category not found', 404);
        }
        return $this->json(true, $model, 'Category retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new CreateCategoryForm();
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $model = new Categories();
        $model->name = $form->name;
        $model->status = $form->status;
        $model->parent_id = $form->parent_id;

        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Category created successfully', 201);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);

    }

    public function actionUpdate($id)
    {
        $model = Categories::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Category not found', 404);
        }

        $form = new UpdateCategoryForm();
        $form->id = $id;

        $data = $this->request->bodyParams;
        if (empty($data)) {
            $data = $this->request->post();
        }
        $form->load($data, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $model->name = $form->name;
        $model->slug = $form->slug;
        $model->status = $form->status;
        $model->parent_id = $form->parent_id;

        try {
            if ($model->save()) {
                return $this->json(true, $model, 'Category updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $model = Categories::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Category not found', 404);
        }
        try {
            if ($model->delete()) {
                return $this->json(true, null, 'Category deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete category', 500);
    }

}
