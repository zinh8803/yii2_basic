<?php
namespace app\models\response\Product;
use app\models\Products;

class ProductResponse extends Products
{
    public function fields()
    {
        return [
            'id',
            'name',
            'category_id',
            'brand_id',
            'slug',
            'image' => function () {
                return $this->primaryResource?->file?->url;
            },
            'description',
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
            'category',
            'brand',
            'productVariants',
            'productAttributes',
        ];
    }
}
