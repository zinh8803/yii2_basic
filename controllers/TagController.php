<?php

namespace app\controllers;

use app\models\forms\Tag\TagForm;
use app\models\search\TagSearch;
use app\models\Tag;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class TagController extends BaseController
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
        $searchModel = new TagSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->formatJson(true, $dataProvider, "Tags retrieved successfully");
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Tag retrieved successfully');
    }

    public function actionCreate()
    {
        $this->checkPermission('tag.create');
        $form = new TagForm([
            'scenario' => TagForm::SCENARIO_CREATE
        ]);
        $form->load($this->request->bodyParams, '');
        if ($form->validate()) {
            $model = new Tag();
            $model->setAttributes($form->attributes, false);
            try {
                if ($model->save(false)) {
                    return $this->formatJson(true, $model, 'Tag created successfully', 201);
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->formatJson(false, $exception->getMessage(), 'Internal server error', 500);
            }
            return $this->formatJson(false, $model->errors, 'Validation failed', 422);
        }
        return $this->formatJson(false, $form->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $this->checkPermission('tag.update');
        $model = $this->findModel($id);
        $form = new TagForm([
            'scenario' => TagForm::SCENARIO_UPDATE
        ]);
        $form->id = $id;
        $form->load($this->request->bodyParams, '');
        if ($form->validate()) {
            $model->setAttributes($form->getAttributes([
                'name',
                'slug',
                'type',
                'description'
            ]), false);
            try {
                if ($model->save(false)) {
                    return $this->formatJson(true, $model, 'Tag updated successfully');
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->formatJson(false, $exception->getMessage(), 'Internal server error', 500);
            }
            return $this->formatJson(false, $model->errors, 'Validation failed', 422);
        }
        return $this->formatJson(false, $form->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $this->checkPermission('tag.delete');
        $model = $this->findModel($id);
        try {
            if ($model->delete()) {
                return $this->formatJson(true, null, 'Tag deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', 500);
        }

        return $this->formatJson(false, null, 'Failed to delete tag', 500);
    }

    public function findModel($id)
    {
        $model = TagForm::find()
            ->where(['id' => $id])
            ->one();
        if (!isset($model)) {
            throw new NotFoundHttpException('Tag not found');
        }
        return $model;
    }
}
