<?php
namespace app\models;

use app\behaviors\Timestamp;

class UserAddress extends base\UserAddress
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
