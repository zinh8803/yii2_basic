<?php

namespace app\models;

use app\behaviors\Timestamp;
use yii\behaviors\TimestampBehavior;

class Cart extends base\Cart
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
