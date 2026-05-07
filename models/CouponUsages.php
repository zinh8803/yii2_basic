<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "coupon_usages".
 *
 * @property int $id
 * @property int $coupon_id
 * @property int $user_id
 * @property int $order_id
 * @property int $used_at
 * @property float $discount_applied
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Coupons $coupon
 * @property Orders $order
 * @property Users $user
 */
class CouponUsages extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupon_usages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coupon_id', 'user_id', 'order_id', 'used_at', 'discount_applied', 'created_at', 'updated_at'], 'required'],
            [['coupon_id', 'user_id', 'order_id', 'used_at', 'created_at', 'updated_at'], 'integer'],
            [['discount_applied'], 'number'],
            [['coupon_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coupons::class, 'targetAttribute' => ['coupon_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Orders::class, 'targetAttribute' => ['order_id' => 'id']],
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
            'coupon_id' => 'Coupon ID',
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'used_at' => 'Used At',
            'discount_applied' => 'Discount Applied',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Coupon]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoupon()
    {
        return $this->hasOne(Coupons::class, ['id' => 'coupon_id']);
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Orders::class, ['id' => 'order_id']);
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
