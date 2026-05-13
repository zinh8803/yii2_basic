<?php
namespace app\models\response;
use app\models\Products;
use app\models\Resources;

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
                $primaryResource = $this->getResources()->andWhere(['is_primary' => 1])->one();
                if ($primaryResource && $primaryResource->file) {
                    return $primaryResource->file->url;
                }
                return null;
            },
            'description',
            'status',
            'created_at',
            'updated_at',
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
