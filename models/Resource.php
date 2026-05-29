<?php
namespace app\models;

use app\behaviors\Timestamp;

class Resource extends base\Resource
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
