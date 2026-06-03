<?php

namespace app\models\forms\Order;

use app\models\User;
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

    public function rules()
    {
        return [
            [['receiver_name', 'receiver_address', 'note'], 'default', 'value' => null],
            [['is_discounted'], 'default', 'value' => 0],
            [['discount_amount'], 'default', 'value' => 0.00],
            [['shipping_fee'], 'default', 'value' => 0],
            [['payment_status'], 'default', 'value' => 'pending'],
            [['status'], 'default', 'value' => 'pending'],
            [['user_id', 'email', 'receiver_phone', 'payment_method'], 'required'],
            [['user_id', 'is_discounted'], 'integer'],
            [['receiver_address', 'note'], 'string'],
            [['shipping_fee', 'discount_amount'], 'number'],
            [['email'], 'email'],
            [['email', 'receiver_name', 'status'], 'string', 'max' => 255],
            [['receiver_phone'], 'string', 'max' => 20],
            [['payment_method', 'payment_status'], 'string', 'max' => 50],
            [['coupon_code'], 'string', 'max' => 50],
            [['order_items'], 'validateOrderItems', 'skipOnEmpty' => false],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
                $variantId = $item['variant_id'] ?? ($item['product_variant_id'] ?? null);
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

        $this->addError($attribute, 'Order item is required.');
    }

    private function isPositiveInteger($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    public function getOrderItems(): array
    {
        if (!empty($this->order_items) && is_array($this->order_items)) {
            $items = [];
            foreach ($this->order_items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $productId = $item['product_id'] ?? null;
                $variantId = $item['variant_id'] ?? ($item['product_variant_id'] ?? null);
                $quantity = $item['quantity'] ?? null;

                if ($this->isPositiveInteger($productId) && $this->isPositiveInteger($variantId) && $this->isPositiveInteger($quantity)) {
                    $items[] = [
                        'product_id' => (int) $productId,
                        'variant_id' => (int) $variantId,
                        'quantity' => (int) $quantity,
                    ];
                }
            }

            return $items;
        }

        return [];
    }
}
