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
    public $coupon_code;
    public $order_items = [];

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
            [['shipping_fee'], 'default', 'value' => 0],
            [['payment_status'], 'default', 'value' => 'pending'],
            [['user_id', 'email', 'receiver_phone', 'status', 'payment_method'], 'required'],
            [['user_id', 'is_discounted'], 'integer'],
            [['receiver_address', 'note'], 'string'],
            [['shipping_fee', 'discount_amount'], 'number'],
            [['email', 'receiver_name', 'status'], 'string', 'max' => 255],
            [['receiver_phone'], 'string', 'max' => 20],
            [['payment_method', 'payment_status'], 'string', 'max' => 50],
            [['coupon_code'], 'string', 'max' => 50],
            [['order_items'], 'validateOrderItems', 'skipOnEmpty' => false],
            [['item_product_id', 'item_variant_id'], 'each', 'rule' => ['integer']],
            [['item_quantity'], 'each', 'rule' => ['integer', 'min' => 1]],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function validateOrderItems($attribute, $params)
    {
        if (!empty($this->order_items)) {
            if (!is_array($this->order_items)) {
                $this->addError($attribute, 'order_items must be an array.');
                return;
            }

            foreach ($this->order_items as $index => $item) {
                if (!is_array($item)) {
                    $this->addError($attribute, 'order_items[' . $index . '] must be an object.');
                    continue;
                }

                $productId = $item['product_id'] ?? null;
                $variantId = $item['variant_id'] ?? null;
                $quantity = $item['quantity'] ?? null;

                if (!$this->isPositiveInteger($productId)) {
                    $this->addError($attribute, 'order_items[' . $index . '].product_id must be a positive integer.');
                }
                if (!$this->isPositiveInteger($variantId)) {
                    $this->addError($attribute, 'order_items[' . $index . '].variant_id must be a positive integer.');
                }
                if (!$this->isPositiveInteger($quantity)) {
                    $this->addError($attribute, 'order_items[' . $index . '].quantity must be a positive integer.');
                }
            }

            return;
        }

        $legacyFields = [$this->item_product_id, $this->item_variant_id, $this->item_quantity];
        $hasLegacyItems = !empty($this->item_product_id) || !empty($this->item_variant_id) || !empty($this->item_quantity);
        if (!$hasLegacyItems) {
            $this->addError($attribute, 'Order item is required.');
            return;
        }

        foreach ($legacyFields as $field) {
            if (!is_array($field)) {
                $this->addError($attribute, 'Order item fields must be arrays.');
                return;
            }
        }

        $count = count($this->item_product_id);
        if ($count !== count($this->item_variant_id) || $count !== count($this->item_quantity)) {
            $this->addError($attribute, 'Order item fields must have the same length.');
        }
    }

    private function isPositiveInteger($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }
}
