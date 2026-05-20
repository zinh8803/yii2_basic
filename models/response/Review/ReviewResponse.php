<?php

namespace app\models\response\Review;

use app\models\Reviews;

class ReviewResponse extends Reviews
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
}
