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
            'created_at',
            'updated_at',
        ];
    }
}
