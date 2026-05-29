<?php

namespace app\models;

use app\behaviors\Timestamp;

class InventoryTransaction extends base\InventoryTransaction
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
