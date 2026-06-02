<?php

namespace app\models;

use app\models\base\BaseOtpEmail;
use app\behaviors\Timestamp;

class OtpEmail extends BaseOtpEmail
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
