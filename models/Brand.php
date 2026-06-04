<?php

namespace app\models;

use app\behaviors\SoftDeleteBehavior;
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
            'is_deleted',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
            'deleted_at' => function () {
                return $this->deleted_at ? date('Y-m-d H:i:s', $this->deleted_at) : null;
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
            'softDelete' => [
                'class' => SoftDeleteBehavior::class,
                'attribute' => 'deleted_at',
                'isDeletedAttribute' => 'is_deleted',
            ],
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'slug',
            ],
        ];
    }
    public function softDelete(): bool
    {
        return $this->getBehavior('softDelete')->softDelete();
    }

    public function restore(): bool
    {
        return $this->getBehavior('softDelete')->restore();
    }
}
