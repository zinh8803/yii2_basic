<?php

namespace app\models\response\Coupon;

use app\models\Coupons;

class CouponResponse extends Coupons
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
            'starts_at',
            'expires_at',
            'is_active',
            'created_at',
            'updated_at',
        ];
    }
}
