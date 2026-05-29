<?php

namespace app\models\response;

use app\models\ProductAttribute;

class ProductAttributeResponse extends ProductAttribute
{
    public function fields()
    {
        return [
            'id',
            'product_id',
            'name',
            'type',
            'slug',
            'attribute_id',
            'is_variant',
            'sort_order',
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
            'attributeValues',
        ];
    }
}
