<?php

namespace app\models;

use app\behaviors\Timestamp;

class OrderItem extends base\OrderItem
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
