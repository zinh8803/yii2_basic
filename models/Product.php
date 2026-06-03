<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseProduct;
use yii\behaviors\SluggableBehavior;

class Product extends BaseProduct
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
            'price' => fn() => $this->productVariants[0]->price ?? null,
            'sale_price' => fn() => $this->productVariants[0]->sale_price ?? null,
            'description',
            'status',
            'rating_avg',
            'rating_count',
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
            'productVariants' => function () {
                return array_map(
                    function ($variant) {
                        return [
                            'id' => $variant->id,
                            'product_id' => $variant->product_id,
                            'name' => $variant->name,
                            'sku' => $variant->sku,
                            'price' => $variant->price,
                            'sale_price' => $variant->sale_price,
                            'cost_price' => $variant->cost_price,
                            'stock' => $variant->stock,
                            'weight' => $variant->weight,
                            'is_active' => $variant->is_active,
                            'image' => $variant->primaryResource ? $variant->primaryResource->file->url : null,
                            'images' => array_map(
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
                                $variant->resources
                            ),
                        ];
                    },
                    $this->productVariants
                );
            },
            'productAttributes',
        ];
    }

    public static function find(): query\ProductQuery
    {
        return new query\ProductQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            Timestamp::class,
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'slug',
            ],
        ];
    }

    public function getResources()
    {
        return $this->hasMany(Resource::class, ['resource_id' => 'id'])
            ->andWhere(['resource_type' => 'product']);
    }

    public function getPrimaryResource()
    {
        return $this->hasOne(Resource::class, ['resource_id' => 'id'])
            ->andWhere([
                'resource_type' => 'product',
                'type' => 'image',
                'is_primary' => 1,
            ]);
    }

}
