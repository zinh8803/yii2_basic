<?php

namespace app\models\response\Category;

use app\models\Categories;

class CategoryResponse extends Categories
{
    public function fields()
    {
        return [
            'id',
            'name',
            'slug',
            'children',
            'status',
            'created_at',
            'updated_at',
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
