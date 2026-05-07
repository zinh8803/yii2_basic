<?php

namespace app\models;

use Yii;

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
 * @property int|null $payment_method
 * @property string|null $payment_status
 * @property string $status
 *
 * @property CouponUsages[] $couponUsages
 * @property OrderItems[] $orderItems
 * @property Users $user
 */
class Orders extends \yii\db\ActiveRecord
{


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
            [['receiver_name', 'receiver_address', 'note', 'created_at', 'updated_at', 'payment_method'], 'default', 'value' => null],
            [['is_discounted'], 'default', 'value' => 0],
            [['discount_amount'], 'default', 'value' => 0.00],
            [['payment_status'], 'default', 'value' => 'pending'],
            [['order_code', 'stacking_id', 'user_id', 'email', 'receiver_phone', 'total', 'status'], 'required'],
            [['user_id', 'is_discounted', 'created_at', 'updated_at', 'payment_method'], 'integer'],
            [['receiver_address', 'note'], 'string'],
            [['total', 'shipping_fee', 'discount_amount'], 'number'],
            [['order_code', 'stacking_id', 'email', 'receiver_name', 'status'], 'string', 'max' => 255],
            [['receiver_phone'], 'string', 'max' => 20],
            [['payment_status'], 'string', 'max' => 50],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
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
            'is_discounted' => 'Is Discounted',
            'total' => 'Total',
            'shipping_fee' => 'Shipping Fee',
            'discount_amount' => 'Discount Amount',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'payment_method' => 'Payment Method',
            'payment_status' => 'Payment Status',
            'status' => 'Status',
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
