<?php
namespace app\models;

use app\models\base\BaseWarehouseUser;
use app\behaviors\Timestamp;

class WarehouseUser extends BaseWarehouseUser
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
