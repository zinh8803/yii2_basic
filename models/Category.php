<?php

namespace app\models;

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
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
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
}
