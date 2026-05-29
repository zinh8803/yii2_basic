<?php
namespace app\models;

use app\behaviors\Timestamp;

class RefreshToken extends base\RefreshToken
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
