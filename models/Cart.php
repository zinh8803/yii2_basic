<?php

namespace app\models;

use app\models\base\BaseCart;
use app\behaviors\Timestamp;
use yii\behaviors\TimestampBehavior;

class Cart extends BaseCart
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
