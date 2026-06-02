<?php
namespace app\models;

use app\models\base\BaseReview;
use app\behaviors\Timestamp;

class Review extends BaseReview
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
