<?php
namespace app\models;

use app\models\base\BasePostProduct;
use app\behaviors\Timestamp;

class PostProduct extends BasePostProduct
{
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
