<?php
namespace app\models;

use app\behaviors\Timestamp;

class PostProduct extends base\PostProduct
{
    public static function find()
    {
        return new query\PostProductQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
