<?php

namespace app\controllers;

use app\components\ResourceImageHelper;
use app\models\forms\Post\PostForm;
use app\models\forms\Post\UpdatePostStatusForm;
use app\models\Post;
use app\models\PostHandler;
use app\models\search\PostSearch;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class PostController extends BaseController
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
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->formatJson(true, $dataProvider, "Posts List");
    }

    public function actionView($id)
    {
        $model = $this->findModel($id, true);
        return $this->formatJson(true, $model, 'Post retrieved successfully');
    }

    public function actionCreate()
    {
        $this->checkPermission('post.create');
        $form = new PostForm(['scenario' => PostForm::SCENARIO_CREATE]);
        $request = Yii::$app->request;
        $isMultipart = strpos((string) $request->getContentType(), 'multipart/form-data') !== false;
        if ($isMultipart) {
            $form->load($request->post(), '');
            $form->imageFile = ResourceImageHelper::getUploadedImageFile($form);
            ResourceImageHelper::logMissingMultipartImage($form->imageFile);
        } else {
            $form->load($this->request->bodyParams, '');
        }
        $userId = Yii::$app->user->id;
        $form->user_id = $userId;

        if ($form->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $post = (new PostHandler())->createFromForm($form);
                if ($post === null) {
                    throw new \RuntimeException('Failed to save post.');
                }

                $transaction->commit();
                return $this->formatJson(true, $post, 'Post created successfully', 201);
            } catch (\Throwable $e) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                Yii::error($e->getMessage(), __METHOD__);
                return $this->formatJson(false, $e->getMessage(), 'Validation failed', 422);
            }
        }

        return $this->formatJson(false, $form->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $this->checkPermission('post.update');
        $post = $this->findModel($id);
        $form = new PostForm(['scenario' => PostForm::SCENARIO_UPDATE]);

        $request = Yii::$app->request;
        $isMultipart = strpos((string) $request->getContentType(), 'multipart/form-data') !== false;
        $data = [];
        if ($isMultipart) {
            $data = $request->post();
            $form->imageFile = ResourceImageHelper::getUploadedImageFile($form);
            ResourceImageHelper::logMissingMultipartImage($form->imageFile);
        } else {
            $data = $request->bodyParams;
        }
        $form->load($data, '');
        $form->id = $post->id;
        $userId = Yii::$app->user->id;
        $form->user_id = $userId;

        if ($isMultipart && empty($data) && $form->imageFile === null) {
            return $this->formatJson(
                false,
                null,
                'PUT/PATCH multipart/form-data is not supported by PHP. Use POST with _method=PUT or send JSON body.',
                400
            );
        }

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = (new PostHandler())->updateFromForm($post, $form);
            if ($post === null) {
                throw new \RuntimeException('Failed to save post.');
            }

            $transaction->commit();
            return $this->formatJson(true, $post, 'Post updated successfully');
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }
    }

    public function actionUpdateStatus($id)
    {
        $this->checkPermission('post.updateStatus');
        $form = new UpdatePostStatusForm();
        $post = $this->findModel($id);
        $form->id = $post->id;
        $form->status = Yii::$app->request->post('status');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        if ((new PostHandler())->updateStatus($post, $form->status)) {
            return $this->formatJson(true, $post, 'Post status updated successfully');
        }

        return $this->formatJson(false, $post->errors, 'Failed to update post status', 422);
    }

    public function actionDelete($id)
    {
        $this->checkPermission('post.delete');
        try {
            $post = $this->findModel($id);
            if ((new PostHandler())->deletePost($post)) {
                return $this->formatJson(true, null, 'Post deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', 500);
        }

        return $this->formatJson(false, null, 'Failed to delete post', 500);
    }

    protected function findModel($id, bool $withRelations = false)
    {
        $query = Post::find()->where(['id' => $id]);
        if ($withRelations) {
            $query->with([
                'taggables.tag',
                'primaryResource.file',
                'postProducts.product.primaryResource.file',
            ]);
        }

        $model = $query->one();
        if (!$model) {
            throw new NotFoundHttpException('Post not found');
        }
        return $model;
    }

}
