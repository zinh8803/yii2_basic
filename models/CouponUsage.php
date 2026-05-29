<?php

namespace app\models;

use app\behaviors\Timestamp;
class CouponUsage extends base\CouponUsage
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
