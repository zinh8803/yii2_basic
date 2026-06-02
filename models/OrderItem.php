<?php

namespace app\models;

use app\models\base\BaseOrderItem;
use app\behaviors\Timestamp;

class OrderItem extends BaseOrderItem
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
