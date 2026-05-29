<?php

namespace app\models\response;

use app\models\Order;

class OrderResponse extends Order
{
    public function fields()
    {
        return [
            'id' => 'id',
            'order_code' => 'order_code',
            'stacking_id' => 'stacking_id',
            'user_id' => 'user_id',
            'email' => 'email',
            'receiver_name' => 'receiver_name',
            'receiver_phone' => 'receiver_phone',
            'receiver_address' => 'receiver_address',
            'note' => 'note',
            'is_discounted' => 'is_discounted',
            'total' => 'total',
            'shipping_fee' => 'shipping_fee',
            'discount_amount' => 'discount_amount',
            'payment_method' => 'payment_method',
            'payment_status' => 'payment_status',
            'status' => 'status',
            'order_items' => 'orderItems',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }
    public function extraFields()
    {
        return [
            'payments',
        ];
    }
}
