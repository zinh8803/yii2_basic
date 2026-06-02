<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseBrand;
use yii\behaviors\SluggableBehavior;


class Brand extends BaseBrand
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
