<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseCart;

class Cart extends BaseCart
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }

    public function fields()
    {
        return [
            'id',
            'user_id',
            'created_at',
            'updated_at',
            'cartItems'

        ];
    }
}
