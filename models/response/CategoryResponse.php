<?php

namespace app\models\response;

use app\models\Category;

class CategoryResponse extends Category
{
    public function fields()
    {
        return [
            'id',
            'name',
            'slug',
            'children',
            'status',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }

    public function extraFields()
    {
        return [
            'parentCategory' => function () {
                return $this->parentCategory ? $this->parentCategory->fields() : null;
            },
        ];
    }
}
