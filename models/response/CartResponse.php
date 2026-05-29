<?php

namespace app\models\response;

use app\models\Cart;

class CartResponse extends Cart
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
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }
}
