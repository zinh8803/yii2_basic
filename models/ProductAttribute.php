<?php
namespace app\models;

use app\behaviors\Timestamp;
use yii\behaviors\SluggableBehavior;

class ProductAttribute extends base\ProductAttribute
{
    public static function find()
    {
        return new query\ProductAttributeQuery(get_called_class());
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
}
