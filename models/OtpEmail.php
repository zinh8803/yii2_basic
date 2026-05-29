<?php

namespace app\models;

use app\behaviors\Timestamp;

class OtpEmail extends base\OtpEmail
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
