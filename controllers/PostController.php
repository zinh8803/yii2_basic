<?php

namespace app\controllers;

use app\models\Files;
use app\models\forms\Post\CreatePostForm;
use app\models\forms\Post\UpdatePostForm;
use app\models\Posts;
use app\models\Resources;
use app\models\response\Post\PostResponse;
use app\models\search\PostSearch;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class PostController extends BaseController
{
    public $modelClass = 'app\models\Posts';
    public function actionIndex()
    {
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Posts retrieved successfully');
    }

    public function actionView($id)
    {
        $model = PostResponse::find()
            ->with(['primaryResource.file'])
            ->where(['id' => $id])
            ->one();
        if (!$model) {
            return $this->json(false, null, 'Post not found', 404);
        }
        return $this->json(true, $model, 'Post retrieved successfully');
    }
    public function actionCreate()
    {
        $form = new CreatePostForm();
        $request = Yii::$app->request;
        $isMultipart = strpos((string) $request->getContentType(), 'multipart/form-data') !== false;
        if ($isMultipart) {
            $form->load($request->post(), '');
            $form->imageFile = $this->getUploadedImageFile($form);
            $this->logMissingMultipartImage($form->imageFile);
        } else {
            $form->load($this->request->bodyParams, '');
        }

        if ($form->validate()) {
            $post = new Posts();
            $post->setAttributes($form->attributes, false);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!$post->save()) {
                    $this->addModelErrors($form, $post);
                    throw new \RuntimeException('Failed to save post.');
                }

                if ($form->imageFile instanceof UploadedFile) {
                    $this->attachImage($post, $form->imageFile, $form);
                }

                $transaction->commit();
                return $this->json(true, $post, 'Post created successfully', 201);
            } catch (\Throwable $e) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                Yii::error($e->getMessage(), __METHOD__);
            }
        }

        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $post = $this->findModel($id);
        $form = new UpdatePostForm();
        $form->id = $post->id;

        $request = Yii::$app->request;
        $isMultipart = strpos((string) $request->getContentType(), 'multipart/form-data') !== false;
        $data = [];

        if ($isMultipart) {
            $data = $request->post();
            $form->imageFile = $this->getUploadedImageFile($form);
            $this->logMissingMultipartImage($form->imageFile);
        } else {
            $data = $request->bodyParams;
        }

        $form->load($data, '');

        if ($isMultipart && empty($data) && $form->imageFile === null) {
            return $this->json(
                false,
                null,
                'PUT/PATCH multipart/form-data is not supported by PHP. Use POST with _method=PUT or send JSON body.',
                400
            );
        }

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->applyFormToPost($post, $form);

            if (!$post->save()) {
                $this->addModelErrors($form, $post);
                throw new \RuntimeException('Failed to save post.');
            }

            if ($form->imageFile instanceof UploadedFile) {
                $this->markPostImagesNonPrimary($post);
                $this->attachImage($post, $form->imageFile, $form, true);
            }

            $transaction->commit();
            return $this->json(true, $post, 'Post updated successfully');
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }
    }

    public function actionDelete($id)
    {
        try {
            $post = $this->findModel($id);
            $this->deletePostImageRecords($post);
            if ($post->delete()) {
                return $this->json(true, null, 'Post deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete post', 500);
    }

    protected function findModel($id)
    {
        if (($model = Posts::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function attachImage(Posts $post, UploadedFile $imageFile, Model $form, bool $isPrimary = true): void
    {
        $uploadDir = Yii::getAlias('@webroot/uploads/posts');
        FileHelper::createDirectory($uploadDir);

        $fileName = Yii::$app->security->generateRandomString(16) . '.' . $imageFile->extension;
        $relativePath = 'uploads/posts/' . $fileName;
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!$imageFile->saveAs($fullPath)) {
            $form->addError('imageFile', 'Failed to upload image.');
            throw new \RuntimeException('Failed to upload image.');
        }

        try {
            $file = $this->createFileRecord($post, $imageFile, $relativePath, $fullPath);
            $this->createImageResource($post, $file, $isPrimary);
        } catch (\Throwable $e) {
            @unlink($fullPath);
            if (!$form->hasErrors('imageFile')) {
                $form->addError('imageFile', 'Failed to save image information.');
            }
            throw $e;
        }
    }

    private function getUploadedImageFile(Model $form): ?UploadedFile
    {
        $file = UploadedFile::getInstance($form, 'imageFile');
        if ($file instanceof UploadedFile) {
            return $file;
        }

        foreach (['imageFile', 'image_file', 'image', 'file'] as $fieldName) {
            $file = UploadedFile::getInstanceByName($fieldName);
            if ($file instanceof UploadedFile) {
                return $file;
            }

            $files = UploadedFile::getInstancesByName($fieldName);
            if (!empty($files)) {
                return $files[0];
            }
        }

        return null;
    }

    private function logMissingMultipartImage(?UploadedFile $imageFile): void
    {
        if ($imageFile !== null) {
            return;
        }

        Yii::warning([
            'message' => 'Multipart request did not include an uploaded image file.',
            'post_keys' => array_keys(Yii::$app->request->post()),
            'file_keys' => array_keys($_FILES),
            'expected_file_keys' => ['imageFile', 'image_file', 'image', 'file'],
        ], __METHOD__);
    }

    private function applyFormToPost(Posts $post, UpdatePostForm $form): void
    {
        if ($form->user_id !== null) {
            $post->user_id = $form->user_id;
        }
        if ($form->title !== null) {
            $post->title = $form->title;
        }
        if ($form->slug !== null) {
            $post->slug = $form->slug;
        }
        if ($form->excerpt !== null) {
            $post->excerpt = $form->excerpt;
        }
        if ($form->content !== null) {
            $post->content = $form->content;
        }
        if ($form->status !== null) {
            $post->status = $form->status;
        }
        if ($form->post_style !== null) {
            $post->post_style = $form->post_style;
        }
        if ($form->meta_title !== null) {
            $post->meta_title = $form->meta_title;
        }
        if ($form->meta_description !== null) {
            $post->meta_description = $form->meta_description;
        }
        if ($form->published_at !== null) {
            $post->published_at = $form->published_at;
        }
    }

    private function createFileRecord(Posts $post, UploadedFile $imageFile, string $relativePath, string $fullPath): Files
    {
        $size = @getimagesize($fullPath);

        $file = new Files();
        $file->user_id = $post->user_id;
        $file->disk = 'local';
        $file->path = $relativePath;
        $file->url = Yii::getAlias('@web/' . $relativePath);
        $file->original_name = $imageFile->name;
        $file->mime_type = $imageFile->type;
        $file->size_bytes = $imageFile->size;
        $file->width = $size ? $size[0] : null;
        $file->height = $size ? $size[1] : null;

        if (!$file->save()) {
            throw new \RuntimeException('Failed to save file record: ' . json_encode($file->errors));
        }

        return $file;
    }

    private function createImageResource(Posts $post, Files $file, bool $isPrimary): void
    {
        $resource = new Resources();
        $resource->file_id = $file->id;
        $resource->resource_type = 'post';
        $resource->resource_id = $post->id;
        $resource->type = 'image';
        $resource->title = $file->original_name;
        $resource->alt_text = null;
        $resource->sort_order = 0;
        $resource->is_primary = $isPrimary ? 1 : 0;

        if (!$resource->save()) {
            throw new \RuntimeException('Failed to save image resource: ' . json_encode($resource->errors));
        }
    }

    private function markPostImagesNonPrimary(Posts $post): void
    {
        Resources::updateAll(
            ['is_primary' => 0],
            [
                'resource_type' => 'post',
                'resource_id' => $post->id,
                'type' => 'image',
                'is_primary' => 1,
            ]
        );
    }

    private function deletePostImageRecords(Posts $post): void
    {
        $resources = Resources::find()
            ->with(['file'])
            ->where([
                'resource_type' => 'post',
                'resource_id' => $post->id,
                'type' => 'image',
            ])
            ->all();

        foreach ($resources as $resource) {
            $relatedRecords = $resource->getRelatedRecords();
            $file = $relatedRecords['file'] ?? null;
            $fullPath = null;
            if ($file && $file->path) {
                $fullPath = Yii::getAlias('@webroot/' . ltrim($file->path, '/\\'));
            }

            $resource->delete();

            if ($file !== null) {
                $file->delete();
            }
            if ($fullPath !== null && is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    private function addModelErrors(Model $form, Model $model): void
    {
        foreach ($model->getErrors() as $attribute => $messages) {
            foreach ($messages as $message) {
                $form->addError($attribute, $message);
            }
        }
    }
}
