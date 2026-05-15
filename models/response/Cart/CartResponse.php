<?php

namespace app\models\response\Cart;

use app\models\Carts;

class CartResponse extends Carts
{
    public function fields()
    {
        return [
            'id',
            'user_id',
            'total',
            'cartItems' => function (self $model) {
                return $model->cartItems;
            },
            'created_at',
            'updated_at',
        ];
    }
}
