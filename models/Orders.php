<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "orders".
 *
 * @property int $id
 * @property string $order_code
 * @property string $stacking_id
 * @property int $user_id
 * @property string $email
 * @property string|null $receiver_name
 * @property string $receiver_phone
 * @property string|null $receiver_address
 * @property string|null $note
 * @property int $is_discounted
 * @property float $total
 * @property float $shipping_fee
 * @property float $discount_amount
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string|null $payment_method
 * @property string|null $payment_status
 * @property string $status
 *
 * @property CouponUsages[] $couponUsages
 * @property OrderItems[] $orderItems
 * @property Users $user
 */
class Orders extends \yii\db\ActiveRecord
{
    public $items_input;
    public $item_product_id;
    public $item_variant_id;
    public $item_quantity;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['receiver_name', 'receiver_address', 'note'], 'default', 'value' => null],
            [['is_discounted'], 'default', 'value' => 0],
            [['discount_amount'], 'default', 'value' => 0.00],
            [['payment_status'], 'default', 'value' => 'pending'],
            [['user_id', 'email', 'receiver_phone', 'total', 'status'], 'required'],
            [['user_id', 'is_discounted'], 'integer'],
            [['receiver_address', 'note'], 'string'],
            [['total', 'shipping_fee', 'discount_amount'], 'number'],
            [['items_input'], 'string'],
            [['item_product_id', 'item_variant_id', 'item_quantity'], 'integer'],
            [['order_code', 'stacking_id', 'email', 'receiver_name', 'status'], 'string', 'max' => 255],
            [['receiver_phone'], 'string', 'max' => 20],
            [['payment_status'], 'string', 'max' => 50],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord) {

            if (!$this->order_code) {
                $this->order_code = $this->generateOrderCode();
            }

            if (!$this->stacking_id) {
                $this->stacking_id = $this->generateTrackingId();
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

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_code' => 'Order Code',
            'stacking_id' => 'Stacking ID',
            'user_id' => 'User ID',
            'email' => 'Email',
            'receiver_name' => 'Receiver Name',
            'receiver_phone' => 'Receiver Phone',
            'receiver_address' => 'Receiver Address',
            'note' => 'Note',
            'status' => 'Status',
            'is_discounted' => 'Is Discounted',
            'total' => 'Total',
            'shipping_fee' => 'Shipping Fee',
            'discount_amount' => 'Discount Amount',
            'payment_method' => 'Payment Method',
            'payment_status' => 'Payment Status',
            'items_input' => 'Order Items',
            'item_product_id' => 'Item Product ID',
            'item_variant_id' => 'Item Variant ID',
            'item_quantity' => 'Item Quantity',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[CouponUsages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCouponUsages()
    {
        return $this->hasMany(CouponUsages::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItems::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

}
