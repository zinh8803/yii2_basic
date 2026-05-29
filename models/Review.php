<?php
namespace app\models;

use app\behaviors\Timestamp;

class Review extends base\Review
{
    public static function find()
    {
        return new query\ReviewQuery(get_called_class());
    }
    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
