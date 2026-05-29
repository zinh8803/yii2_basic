<?php

namespace app\models;

use app\behaviors\Timestamp;
use yii\behaviors\SluggableBehavior;


class Category extends base\Category
{
    public static function find()
    {
        return new query\CategoryQuery(get_called_class());
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
