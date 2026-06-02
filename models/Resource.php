<?php
namespace app\models;

use app\models\base\BaseResource;
use app\behaviors\Timestamp;

class Resource extends BaseResource
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
