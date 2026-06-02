<?php
namespace app\models;

use app\models\base\BaseTag;
use app\behaviors\Timestamp;
use yii\behaviors\SluggableBehavior;

class Tag extends BaseTag
{
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
