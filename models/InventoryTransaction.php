<?php

namespace app\models;

use app\models\base\BaseInventoryTransaction;
use app\behaviors\Timestamp;

class InventoryTransaction extends BaseInventoryTransaction
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
