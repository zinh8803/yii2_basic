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
                $relatedRecords = $this->getRelatedRecords();
                $primaryResource = $relatedRecords['primaryResource'] ?? null;
                if ($primaryResource === null && !$this->isRelationPopulated('primaryResource')) {
                    $primaryResource = $this->getPrimaryResource()->with(['file'])->one();
                }

                if ($primaryResource && $primaryResource->file) {
                    return $primaryResource->file->url;
                }
                return null;
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
            'productAttributes.attributeValues',
        ];
    }
}
