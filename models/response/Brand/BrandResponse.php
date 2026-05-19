<?php

namespace app\models\response\Brand;

use app\models\Brands;

class BrandResponse extends Brands
{
    public function fields()
    {
        return [
            'id',
            'name',
            'slug',
            'status',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }
}
