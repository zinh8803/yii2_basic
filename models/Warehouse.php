<?php
namespace app\models;

use app\behaviors\Timestamp;

class Warehouse extends base\Warehouse
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
