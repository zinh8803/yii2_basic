<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseOrder;
use Yii;

class Order extends BaseOrder
{
    public function fields()
    {
        return [
            'id' => 'id',
            'order_code' => 'order_code',
            'stacking_id' => 'stacking_id',
            'user_id' => 'user_id',
            'email' => 'email',
            'receiver_name' => 'receiver_name',
            'receiver_phone' => 'receiver_phone',
            'receiver_address' => 'receiver_address',
            'note' => 'note',
            'is_discounted' => 'is_discounted',
            'total' => 'total',
            'shipping_fee' => 'shipping_fee',
            'discount_amount' => 'discount_amount',
            'payment_method' => 'payment_method',
            'payment_status' => 'payment_status',
            'status' => 'status',
            'order_items' => 'orderItems',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }

    public function extraFields()
    {
        return [
            'payments',
        ];
    }

    public static function find()
    {
        return new query\OrderQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            Timestamp::class
        ];
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            if (!($this instanceof OrderSearch)) {
                if (!$this->order_code) {
                    $this->order_code = $this->generateOrderCode();
                }

                if (!$this->stacking_id) {
                    $this->stacking_id = $this->generateTrackingId();
                }
            }
        }

        return parent::beforeValidate();
    }

    protected function generateOrderCode()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(
                Yii::$app->security->generateRandomString(6)
            );
    }

    protected function generateTrackingId()
    {
        return 'TRK-' . strtoupper(
                Yii::$app->security->generateRandomString(10)
            );
    }
}
