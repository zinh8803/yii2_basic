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
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }
}
