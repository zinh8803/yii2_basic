<?php

namespace app\controllers;

use app\models\Tags;
use app\models\forms\Tag\CreateTagForm;
use app\models\forms\Tag\UpdateTagForm;
use app\models\response\Tag\TagResponse;
use app\models\search\TagSearch;
use Yii;

class TagController extends BaseController
{
    public $modelClass = 'app\models\Tags';
    public function actionIndex()
    {
        $searchModel = new TagSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Tags retrieved successfully');
    }
    public function actionView($id)
    {
        $model = TagResponse::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Tag not found', 404);
        }
        return $this->json(true, $model, 'Tag retrieved successfully');
    }
    public function actionCreate()
    {
        $form = new CreateTagForm();
        $form->load($this->request->bodyParams, '');

        if ($form->validate()) {
            $model = new Tags();
            $model->setAttributes($form->attributes, false);
            try {
                if ($model->save()) {
                    return $this->json(true, $model, 'Tag created successfully', 201);
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->json(false, null, 'Internal server error', 500);
            }
            return $this->json(false, $model->errors, 'Validation failed', 422);
        }
        return $this->json(false, $form->errors, 'Validation failed', 422);
    }
    public function actionUpdate($id)
    {
        $model = TagResponse::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Tag not found', 404);
        }
        $form = new UpdateTagForm();
        $form->load($this->request->bodyParams, '');
        if ($form->validate()) {
            $model->setAttributes($form->attributes, false);
            try {
                if ($model->save()) {
                    return $this->json(true, $model, 'Tag updated successfully');
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->json(false, null, 'Internal server error', 500);
            }
            return $this->json(false, $model->errors, 'Validation failed', 422);
        }
        return $this->json(false, $form->errors, 'Validation failed', 422);
    }
    public function actionDelete($id)
    {
        $model = TagResponse::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Tag not found', 404);
        }
        try {
            if ($model->delete()) {
                return $this->json(true, null, 'Tag deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete tag', 500);
    }

}
