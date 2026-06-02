<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseAttributeValue;
use yii\behaviors\SluggableBehavior;
class AttributeValue extends BaseAttributeValue
{
    public static function find()
    {
        return new query\AttributeValueQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            Timestamp::class,
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'value',
                'slugAttribute' => 'slug',
            ],
        ];
    }
}
