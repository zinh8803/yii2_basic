<?php
namespace app\models;

use app\models\base\BaseTaggable;
use app\behaviors\Timestamp;

class Taggable extends BaseTaggable
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
