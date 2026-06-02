<?php
namespace app\models;

use app\models\base\BaseWarehouse;
use app\behaviors\Timestamp;

class Warehouse extends BaseWarehouse
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
