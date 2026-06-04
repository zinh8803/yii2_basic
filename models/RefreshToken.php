<?php
namespace app\models;

use app\models\base\BaseRefreshToken;
use app\behaviors\Timestamp;

class RefreshToken extends BaseRefreshToken
{
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
