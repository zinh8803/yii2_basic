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
            'image' => function () {
                $resource = $this->primaryResource;
                return $resource ? $resource->file->url : null;
            },
            'images' => function () {
                return array_map(
                    function ($resource) {
                        return [
                            'id' => $resource->id,
                            'file_id' => $resource->file_id,
                            'url' => $resource->file->url,
                            'title' => $resource->title,
                            'alt_text' => $resource->alt_text,
                            'sort_order' => $resource->sort_order,
                            'is_primary' => $resource->is_primary,
                        ];
                    },
                    $this->resources
                );
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
