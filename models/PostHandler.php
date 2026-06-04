<?php

namespace app\models;

use app\components\ResourceImageHelper;
use app\models\forms\Post\PostForm;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class PostHandler extends Post
{
    public function createFromForm(PostForm $form): ?Post
    {
        $post = new Post();
        $this->applyCreateFormToPost($post, $form);

        if (!$post->save()) {
            $this->addModelErrors($form, $post);
            return null;
        }

        $productIds = $this->normalizeIdArray($form->products);
        $tagIds = $this->normalizeIdArray($form->tag_ids);
        $products = $this->syncPostProducts($post, $productIds, false);
        $tags = $this->syncPostTags($post, $tagIds, false);
        $primaryResource = $this->attachPostImageFromForm($post, $form);
        $this->populatePostRelations($post, $productIds, $products, $tagIds, $tags, $primaryResource);

        return $post;
    }

    public function updateFromForm(Post $post, PostForm $form): ?Post
    {
        $this->applyUpdateFormToPost($post, $form);

        if (!$post->save(true, [
            'title',
            'slug',
            'excerpt',
            'content',
            'status',
            'post_style',
            'meta_title',
            'meta_description',
            'published_at',
            'updated_at',
        ])) {
            $this->addModelErrors($form, $post);
            return null;
        }

        $productIds = $form->products === null ? null : $this->normalizeIdArray($form->products);
        $tagIds = $form->tag_ids === null ? null : $this->normalizeIdArray($form->tag_ids);
        $products = $productIds === null ? null : $this->syncPostProducts($post, $productIds);
        $tags = $tagIds === null ? null : $this->syncPostTags($post, $tagIds);

        $primaryResource = $this->attachPostImageFromForm($post, $form, true);
        $this->populatePostRelations($post, $productIds, $products, $tagIds, $tags, $primaryResource);

        return $post;
    }

    public function updateStatus(Post $post, string $status): bool
    {
        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            $post->addError('status', 'Invalid status value');
            return false;
        }

        $post->status = $status;
        $post->published_at = $status === 'published' ? time() : null;

        return $post->save();
    }

    public function deletePost(Post $post): bool
    {
        ResourceImageHelper::deleteImageResourceLinks('post', $post->id);
        return (bool) $post->delete();
    }

    private function applyCreateFormToPost(Post $post, PostForm $form): void
    {
        $post->user_id = $form->user_id;
        $post->title = $form->title;
        if (!empty($form->slug)) {
            $post->slug = $form->slug;
        }
        $post->excerpt = $form->excerpt;
        $post->content = $form->content;
        $post->status = $form->status;
        $post->post_style = $form->post_style;
        $post->meta_title = $form->meta_title;
        $post->meta_description = $form->meta_description;
        $post->published_at = $form->published_at ?? ($form->status === 'published' ? time() : null);
    }

    private function applyUpdateFormToPost(Post $post, PostForm $form): void
    {
        foreach ([
                     'title',
                     'excerpt',
                     'content',
                     'status',
                     'post_style',
                     'meta_title',
                     'meta_description',
                     'published_at',
                 ] as $attribute) {
            if ($form->{$attribute} !== null) {
                $post->{$attribute} = $form->{$attribute};
            }
        }

        if ($form->status === 'published' && $form->published_at === null && empty($post->published_at)) {
            $post->published_at = time();
        }

        if (!empty($form->slug)) {
            $post->slug = $form->slug;
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

    private function attachPostImageFromForm(
        Post     $post,
        PostForm $form,
        bool     $replacePrimary = false
    ): ?Resource
    {
        if (
            !$form->imageFile instanceof UploadedFile
            && empty($form->image_file_id)
            && empty($form->image_resource_id)
        ) {
            return null;
        }

        if ($replacePrimary) {
            ResourceImageHelper::markImagesNonPrimary('post', $post->id);
        }

        if ($form->imageFile instanceof UploadedFile) {
            return ResourceImageHelper::attachImage('post', $post->id, $post->user_id, 'uploads/posts', $form->imageFile, $form);
        }

        if (!empty($form->image_resource_id)) {
            return ResourceImageHelper::attachExistingImageResource('post', $post->id, (int) $form->image_resource_id);
        }

        return ResourceImageHelper::attachExistingImageFile('post', $post->id, (int) $form->image_file_id);
    }

    private function syncPostProducts(Post $post, array $productIds, bool $deleteExisting = true): array
    {
        if ($deleteExisting) {
            PostProduct::deleteAll(['post_id' => $post->id]);
        }

        if (empty($productIds)) {
            return [];
        }

        $products = Product::find()
            ->with(['primaryResource.file'])
            ->where(['id' => $productIds])
            ->indexBy('id')
            ->all();

        $missingIds = array_diff($productIds, array_map('intval', array_keys($products)));
        if (!empty($missingIds)) {
            throw new \RuntimeException('Product not found: ' . implode(', ', $missingIds));
        }

        $rows = [];
        $time = time();
        $sortOrder = 0;
        foreach ($productIds as $productId) {
            $rows[] = [$post->id, (int) $productId, $sortOrder++, null, $time, $time];
        }

        Yii::$app->db->createCommand()->batchInsert(
            PostProduct::tableName(),
            ['post_id', 'product_id', 'sort_order', 'note', 'created_at', 'updated_at'],
            $rows
        )->execute();

        return $products;
    }

    private function syncPostTags(Post $post, array $tagIds, bool $deleteExisting = true): array
    {
        if ($deleteExisting) {
            Taggable::deleteAll([
                'post_id' => $post->id,
                'type' => 'post',
            ]);
        }

        if (empty($tagIds)) {
            return [];
        }

        $tags = Tag::find()
            ->where(['id' => $tagIds])
            ->indexBy('id')
            ->all();

        $missingIds = array_diff($tagIds, array_map('intval', array_keys($tags)));
        if (!empty($missingIds)) {
            throw new \RuntimeException('Tag not found: ' . implode(', ', $missingIds));
        }

        $rows = [];
        $time = time();
        foreach ($tagIds as $tagId) {
            $rows[] = [(int) $tagId, $post->id, 'post', $time, $time];
        }

        Yii::$app->db->createCommand()->batchInsert(
            Taggable::tableName(),
            ['tag_id', 'post_id', 'type', 'created_at', 'updated_at'],
            $rows
        )->execute();

        return $tags;
    }

    private function populatePostRelations(
        Post      $post,
        ?array    $productIds,
        ?array    $products,
        ?array    $tagIds,
        ?array    $tags,
        ?Resource $primaryResource
    ): void
    {
        if ($tagIds !== null && $tags !== null) {
            $taggables = [];
            foreach ($tagIds as $tagId) {
                $tag = $tags[(int) $tagId] ?? null;
                if ($tag === null) {
                    continue;
                }

                $taggable = new Taggable();
                $taggable->setAttributes([
                    'tag_id' => (int) $tagId,
                    'post_id' => $post->id,
                    'type' => 'post',
                ], false);
                $taggable->populateRelation('tag', $tag);
                $taggables[] = $taggable;
            }
            $post->populateRelation('taggables', $taggables);
        }

        if ($primaryResource !== null) {
            $post->populateRelation('primaryResource', $primaryResource);
        } elseif (!$post->isRelationPopulated('primaryResource')) {
            $post->populateRelation('primaryResource', $post->getPrimaryResource()->with(['file'])->one());
        }

        if ($productIds !== null && $products !== null) {
            $postProducts = [];
            $sortOrder = 0;
            foreach ($productIds as $productId) {
                $product = $products[(int) $productId] ?? null;
                if ($product === null) {
                    continue;
                }

                $postProduct = new PostProduct();
                $postProduct->setAttributes([
                    'post_id' => $post->id,
                    'product_id' => (int) $productId,
                    'sort_order' => $sortOrder++,
                ], false);
                $postProduct->populateRelation('product', $product);
                $postProducts[] = $postProduct;
            }
            $post->populateRelation('postProducts', $postProducts);
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
