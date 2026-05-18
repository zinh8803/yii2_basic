<?php

namespace app\models\forms\Coupon;

use app\models\Coupons;
use yii\base\Model;

class CreateCouponForm extends Model
{
    public $code;
    public $type;
    public $value;
    public $used_count;
    public $min_order_value;
    public $max_discount;
    public $max_usage;
    public $is_active;
    public $starts_at;
    public $expires_at;

    public function rules()
    {
        return [
            [['used_count'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['code', 'type', 'value', 'min_order_value', 'max_discount', 'max_usage', 'starts_at', 'expires_at'], 'required'],
            [['value', 'min_order_value', 'max_discount'], 'number'],
            [['max_usage', 'used_count', 'is_active'], 'integer'],
            [['code'], 'string', 'max' => 50],
            [['type'], 'string', 'max' => 20],
            [['starts_at', 'expires_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['expires_at'], 'compare', 'compareAttribute' => 'starts_at', 'operator' => '>'],
            [['code'], 'unique', 'targetClass' => Coupons::class, 'targetAttribute' => 'code'],
        ];
    }
}
