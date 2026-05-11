<?php

namespace app\models\forms\Cart;

use yii\base\Model;

class AddToCartForm extends Model
{
    public $user_id;
    public $product_id;
    public $product_variant_id;
    public $quantity;

    public function rules()
    {
        return [
            [['user_id', 'product_id', 'product_variant_id', 'quantity'], 'required'],

            [['user_id', 'product_id', 'product_variant_id', 'quantity'], 'integer'],

            [['quantity'], 'integer', 'min' => 1],
        ];
    }
}
