<?php

namespace app\models\response\Post;

use app\models\Posts;

class PostResponse extends Posts
{
    public function fields()
    {
        return [
            'id' => 'id',
            'author' => 'user_id',
            'title' => 'title',
            'slug' => 'slug',
            'image' => function () {
                return $this->primaryResource?->file?->url;
            },
            'tags' => function () {
                return array_map(
                    fn($taggable) => $taggable->tag,
                    $this->taggables
                );
            },
            'products' => function () {
                return array_map(
                    function ($postProduct) {

                        $product = $postProduct->product;

                        if (!$product) {
                            return null;
                        }

                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'slug' => $product->slug,
                            'image' => $product->primaryResource ? $product->primaryResource->file->url : null,
                        ];
                    },
                    $this->postProducts
                );
            },
            'excerpt' => 'excerpt',
            'content' => 'content',
            'status' => 'status',
            'post_style' => 'post_style',
            'meta_title' => 'meta_title',
            'meta_description' => 'meta_description',
            'published_at' => function () {
                return $this->published_at ? date('Y-m-d H:i:s', $this->published_at) : null;
            },
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }
}
