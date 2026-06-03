<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseReview;

class Review extends BaseReview
{
    public function fields()
    {
        return [
            'id',
            'product_id',
            'user_id',
            'rating',
            'comment',
            'is_approved',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }

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
