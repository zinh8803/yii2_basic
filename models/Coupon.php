<?php

namespace app\models;

use app\behaviors\Timestamp;

class Coupon extends base\Coupon
{
    public static function find()
    {
        return new query\CouponQuery(get_called_class());
    }
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
    // public function afterFind()
    // {
    //     parent::afterFind();

    //     $this->starts_at = date('Y-m-d\TH:i', $this->starts_at);
    //     $this->expires_at = date('Y-m-d\TH:i', $this->expires_at);
    // }
}
