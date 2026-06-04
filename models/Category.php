<?php

namespace app\models;

use app\behaviors\SoftDeleteBehavior;
use app\behaviors\Timestamp;
use app\models\base\BaseCategory;
use yii\behaviors\SluggableBehavior;


class Category extends BaseCategory
{
    public function fields()
    {
        return [
            'id',
            'name',
            'slug',
            //   'children',
            'children' => function ($model) {
                return $model->children;
            },
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
            }
        ];
    }

    public function extraFields()
    {
        return [
            'parentCategory' => function () {
                return $this->parentCategory ? $this->parentCategory->fields() : null;
            },
        ];
    }

    public static function find()
    {
        return new query\CategoryQuery(get_called_class());
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

    /**
     * Gets query for [[Children]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Category::class, ['parent_id' => 'id'])->andWhere(['status' => 1]);
    }

    public function hasChildren()
    {
        return $this->getChildren()->exists();
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
