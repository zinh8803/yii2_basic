<?php
namespace app\models\response\ProductVariant;

use app\models\ProductVariants;

class ProductVariantResponse extends ProductVariants
{
    public function fields()
    {
        return [
            'id',
            'product_id',
            'name',
            'sku',
            'price',
            'sale_price',
            'cost_price',
            'stock',
            'weight',
            'is_active',
            'created_at',
            'updated_at',
        ];
    }
}
