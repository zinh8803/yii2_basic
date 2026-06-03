<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BasePostProduct;

class PostProduct extends BasePostProduct
{
    public function fields()
    {
        return [
            'id' => 'id',
            'post_id' => 'post_id',
            'product_id' => 'product_id',
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
        return new query\PostProductQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }
}
