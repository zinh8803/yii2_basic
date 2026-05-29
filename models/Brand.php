<?php

namespace app\models;

use app\behaviors\Timestamp;
use yii\behaviors\SluggableBehavior;


class Brand extends base\Brand
{
    public static function find()
    {
        return new query\BrandQuery(get_called_class());
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
