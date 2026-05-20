<?php

namespace app\controllers;

use app\helpers\ResourceImageHelper;
use app\models\forms\Post\CreatePostForm;
use app\models\forms\Post\UpdatePostForm;
use app\models\forms\Post\UpdatePostStatusForm;
use app\models\Posts;
use app\models\PostProducts;
use app\models\Taggables;
use app\models\response\Post\PostResponse;
use app\models\search\PostSearch;
use Yii;
use yii\base\Model;
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
            ->with([
                'taggables.tag',
                'primaryResource.file',
                'postProducts.product.primaryResource.file',
            ])
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
            $form->imageFile = ResourceImageHelper::getUploadedImageFile($form);
            ResourceImageHelper::logMissingMultipartImage($form->imageFile);
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

                $this->syncPostProducts($post, $this->normalizeIdArray($form->products));
                $this->syncPostTags($post, $this->normalizeIdArray($form->tag_ids));
                if ($form->imageFile instanceof UploadedFile) {
                    ResourceImageHelper::attachImage('post', $post->id, $post->user_id, 'uploads/posts', $form->imageFile, $form);
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
            $form->imageFile = ResourceImageHelper::getUploadedImageFile($form);
            ResourceImageHelper::logMissingMultipartImage($form->imageFile);
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

            if ($form->products !== null) {
                $this->syncPostProducts($post, $this->normalizeIdArray($form->products));
            }
            if ($form->tag_ids !== null) {
                $this->syncPostTags($post, $this->normalizeIdArray($form->tag_ids));
            }
            if ($form->imageFile instanceof UploadedFile) {
                ResourceImageHelper::markImagesNonPrimary('post', $post->id);
                ResourceImageHelper::attachImage('post', $post->id, $post->user_id, 'uploads/posts', $form->imageFile, $form);
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

    public function actionUpdateStatus($id)
    {
        $form = new UpdatePostStatusForm();
        $post = $this->findModel($id);
        $form->id = $post->id;
        $form->status = Yii::$app->request->post('status');

        if (!in_array($form->status, ['draft', 'published', 'archived'])) {
            return $this->json(false, null, 'Invalid status value', 422);
        }

        $post->status = $form->status;
        $post->published_at = $form->status === 'published' ? time() : null;
        if ($post->save()) {
            return $this->json(true, $post, 'Post status updated successfully');
        }

        return $this->json(false, $post->errors, 'Failed to update post status', 422);
    }

    public function actionDelete($id)
    {
        try {
            $post = $this->findModel($id);
            ResourceImageHelper::deleteImageRecords('post', $post->id);
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

    private function normalizeIdArray($value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value)) {
            $items = preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $items = [$value];
        }

        $ids = [];
        foreach ($items as $item) {
            $id = (int) $item;
            if ($id > 0) {
                $ids[$id] = true;
            }
        }

        return array_keys($ids);
    }

    private function syncPostProducts(Posts $post, array $productIds): void
    {
        PostProducts::deleteAll(['post_id' => $post->id]);
        if (empty($productIds)) {
            return;
        }

        $sortOrder = 0;
        foreach ($productIds as $productId) {
            $postProduct = new PostProducts();
            $postProduct->setAttributes([
                'post_id' => $post->id,
                'product_id' => (int) $productId,
                'sort_order' => $sortOrder,
            ], false);

            if (!$postProduct->save()) {
                throw new \RuntimeException('Failed to save post product: ' . json_encode($postProduct->errors));
            }

            $sortOrder++;
        }
    }

    private function syncPostTags(Posts $post, array $tagIds): void
    {
        Taggables::deleteAll([
            'post_id' => $post->id,
            'type' => 'post',
        ]);

        if (empty($tagIds)) {
            return;
        }

        foreach ($tagIds as $tagId) {
            $taggable = new Taggables();
            $taggable->setAttributes([
                'tag_id' => (int) $tagId,
                'post_id' => $post->id,
                'type' => 'post',
            ], false);

            if (!$taggable->save()) {
                throw new \RuntimeException('Failed to save taggable: ' . json_encode($taggable->errors));
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
