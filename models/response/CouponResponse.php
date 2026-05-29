<?php

namespace app\models\response;

use app\models\Coupon;

class CouponResponse extends Coupon
{
    public function fields()
    {
        return [
            'id',
            'code',
            'type',
            'value',
            'min_order_value',
            'max_discount',
            'max_usage',
            'used_count',
            'starts_at' => function () {
                return date('Y-m-d H:i:s', $this->starts_at);
            },
            'expires_at' => function () {
                return date('Y-m-d H:i:s', $this->expires_at);
            },
            'is_active',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }
}
