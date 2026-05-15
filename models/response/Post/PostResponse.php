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
                $primaryResource = $this->getResources()->andWhere(['is_primary' => 1])->one();
                if ($primaryResource && $primaryResource->file) {
                    return $primaryResource->file->url;
                }
                return null;
            },
            'excerpt' => 'excerpt',
            'content' => 'content',
            'status' => 'status',
            'post_style' => 'post_style',
            'meta_title' => 'meta_title',
            'meta_description' => 'meta_description',
            'published_at' => 'published_at',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }
}
