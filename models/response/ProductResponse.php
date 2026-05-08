<?php
namespace app\models\response;
use app\models\Products;
use yii\base\Model;

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
            'description',
            'status',
            'created_at',
            'updated_at',
            'category' => function ($model) {
                return $model->category ? $model->category->name : null;
            },
            'brand' => function ($model) {
                return $model->brand ? $model->brand->name : null;
            },
        ];
    }
}
