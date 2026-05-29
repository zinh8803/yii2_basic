<?php

namespace app\models;

use app\behaviors\Timestamp;


class CartItem extends base\CartItem
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
