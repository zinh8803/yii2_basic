<?php

namespace app\models\forms\Order;

use app\models\Users;
use yii\base\Model;

class CreateOrderForm extends Model
{
    public $user_id;
    public $email;
    public $receiver_name;
    public $receiver_phone;
    public $receiver_address;
    public $note;
    public $is_discounted;
    public $shipping_fee;
    public $discount_amount;
    public $payment_method;
    public $payment_status;
    public $status;

    // Order item fields (arrays)
    public $item_product_id = [];
    public $item_variant_id = [];
    public $item_quantity = [];

    public function rules()
    {
        return [
            [['receiver_name', 'receiver_address', 'note'], 'default', 'value' => null],
            [['is_discounted'], 'default', 'value' => 0],
            [['discount_amount'], 'default', 'value' => 0.00],
            [['payment_status'], 'default', 'value' => 'pending'],
            [['user_id', 'email', 'receiver_phone', 'status'], 'required'],
            [['user_id', 'is_discounted'], 'integer'],
            [['receiver_address', 'note'], 'string'],
            [['shipping_fee', 'discount_amount'], 'number'],
            [['email', 'receiver_name', 'status'], 'string', 'max' => 255],
            [['receiver_phone'], 'string', 'max' => 20],
            [['payment_method', 'payment_status'], 'string', 'max' => 50],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],

            // Validate each item row
            ['item_product_id', 'each', 'rule' => ['integer']],
            ['item_variant_id', 'each', 'rule' => ['integer']],
            ['item_quantity', 'each', 'rule' => ['integer', 'min' => 1]],
        ];
    }
}
