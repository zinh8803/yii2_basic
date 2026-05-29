<?php
namespace app\models;

use app\behaviors\Timestamp;

class WarehouseUser extends base\WarehouseUser
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
