<?php

namespace app\models;

use app\behaviors\Timestamp;

class Payment extends base\Payment
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
