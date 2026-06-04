<?php
namespace app\models;

use app\models\base\BaseUserAddress;
use app\behaviors\Timestamp;

class UserAddress extends BaseUserAddress
{

    public static function find()
    {
        return new query\UserAddressQuery(get_called_class());
    }
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
