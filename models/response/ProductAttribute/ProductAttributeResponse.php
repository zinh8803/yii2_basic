<?php

namespace app\models\response\ProductAttribute;

use app\models\ProductAttributes;

class ProductAttributeResponse extends ProductAttributes
{
    public function fields()
    {
        return [
            'id',
            'product_id',
            'name',
            'type',
            'slug',
            'attributeValues',
            'is_variant',
            'sort_order',
            'created_at',
            'updated_at',
        ];
    }
    public function extraFields()
    {
        return [
            'attributeValues',
        ];
    }
}
