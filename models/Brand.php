<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseBrand;
use yii\behaviors\SluggableBehavior;


class Brand extends BaseBrand
{
    public function fields()
    {
        return [
            'id',
            'name',
            'slug',
            'status',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }

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
