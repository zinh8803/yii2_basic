<?php

namespace app\models;

use app\models\base\BasePayment;
use app\behaviors\Timestamp;

class Payment extends BasePayment
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
