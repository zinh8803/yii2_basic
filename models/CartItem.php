<?php

namespace app\models;

use app\models\base\BaseCartItem;
use app\behaviors\Timestamp;


class CartItem extends BaseCartItem
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
