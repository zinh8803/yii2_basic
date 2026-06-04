<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseCart;

class Cart extends BaseCart
{
    public function fields()
    {
        return [
            'id',
            'user_id',
            'total',
            'cartItems',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }

    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }

}
