<?php

namespace app\models\response;

use app\models\PostProduct;

class PostProductResponse extends PostProduct
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
}

