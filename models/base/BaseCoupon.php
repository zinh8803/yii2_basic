<?php

namespace app\models\base;

use app\models\CouponUsage;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "coupons".
 *
 * @property int $id
 * @property string $code
 * @property string $type
 * @property float $value
 * @property float $min_order_value
 * @property float $max_discount
 * @property int $max_usage
 * @property int $used_count
 * @property int $starts_at
 * @property int $expires_at
 * @property int $is_active
 * @property int $created_at
 * @property int $updated_at
 *
 * @property CouponUsage[] $couponUsages
 */
class BaseCoupon extends ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupons';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['used_count'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['code', 'type', 'value', 'min_order_value', 'max_discount', 'max_usage', 'starts_at', 'expires_at'], 'required'],
            [['value', 'min_order_value', 'max_discount'], 'number'],
            [['max_usage', 'used_count', 'starts_at', 'expires_at', 'is_active'], 'integer'],
            [['code'], 'string', 'max' => 50],
            [['type'], 'string', 'max' => 20],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'type' => 'Type',
            'value' => 'Value',
            'min_order_value' => 'Min Order Value',
            'max_discount' => 'Max Discount',
            'max_usage' => 'Max Usage',
            'used_count' => 'Used Count',
            'starts_at' => 'Starts At',
            'expires_at' => 'Expires At',
            'is_active' => 'Is Active',
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
        return $this->hasMany(CouponUsage::class, ['coupon_id' => 'id']);
    }

}
