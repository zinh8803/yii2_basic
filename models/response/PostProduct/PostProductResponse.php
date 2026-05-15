<?php

namespace app\models\response\PostProduct;

use app\models\PostProducts;

class PostProductResponse extends PostProducts
{
    public function fields()
    {
        return [
            'id' => 'id',
            'post_id' => 'post_id',
            'product_id' => 'product_id',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }
}

