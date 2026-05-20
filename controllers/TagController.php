<?php

namespace app\controllers;

use app\models\Tags;
use app\models\forms\Tag\CreateTagForm;
use app\models\forms\Tag\UpdateTagForm;
use app\models\response\Tag\TagResponse;
use app\models\search\TagSearch;
use Yii;
use yii\caching\TagDependency;

class TagController extends BaseController
{
    public $modelClass = 'app\models\Tags';
    public function actionIndex()
    {
        $cacheKey = 'tags_index_' . md5(json_encode($this->request->queryParams));
        $data = Yii::$app->cache->getOrSet(
            $cacheKey,
            function () {
                $searchModel = new TagSearch();
                $dataProvider = $searchModel->search($this->request->queryParams);
                return $this->paginate($dataProvider->query);
            },
            60,
            new TagDependency(['tags' => 'tags_index'])
        );

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
                    TagDependency::invalidate(Yii::$app->cache, 'tags_index');
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
        $form->id = $model->id;
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
