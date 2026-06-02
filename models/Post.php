<?php

namespace app\models;

use app\models\base\BasePost;
use app\behaviors\Timestamp;
use yii\behaviors\SluggableBehavior;
class Post extends BasePost
{
    public static function find()
    {
        return new query\PostQuery(get_called_class());
    }
    public function behaviors()
    {
        return [
            Timestamp::class,
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'title',
                'slugAttribute' => 'slug',
            ],
        ];
    }

    public function getResources()
    {
        return $this->hasMany(Resource::class, ['resource_id' => 'id'])
            ->andWhere(['resource_type' => 'post']);
    }

    public function getPrimaryResource()
    {
        return $this->hasOne(Resource::class, ['resource_id' => 'id'])
            ->andWhere([
                'resource_type' => 'post',
                'type' => 'image',
                'is_primary' => 1,
            ]);
    }
}
