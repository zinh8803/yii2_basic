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
