<?php
namespace app\models;

use app\behaviors\Timestamp;

class Taggable extends base\Taggable
{

    public static function find()
    {
        return new query\TaggableQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
