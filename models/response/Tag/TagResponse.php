<?php

namespace app\models\response\Tag;

use app\models\Tags;

class TagResponse extends Tags
{
    public function fields()
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'slug' => 'slug',
            'type' => 'type',
            'description' => 'description',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }
}
