<?php

namespace app\models;

use app\models\base\BaseCouponUsage;
use app\behaviors\Timestamp;
class CouponUsage extends BaseCouponUsage
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
