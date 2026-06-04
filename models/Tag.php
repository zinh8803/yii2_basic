<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseTag;
use yii\behaviors\SluggableBehavior;

class Tag extends BaseTag
{
    public function fields()
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'slug' => 'slug',
            'type' => 'type',
            'description' => 'description',
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
        return new query\TagQuery(get_called_class());
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
