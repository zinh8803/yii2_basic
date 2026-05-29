<?php
namespace app\models;

use app\behaviors\Timestamp;

class user extends base\user
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
