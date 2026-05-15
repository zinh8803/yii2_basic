<?php

namespace app\models\forms\Coupon;

use app\models\Coupons;
use yii\base\Model;

class UpdateCouponForm extends Model
{
    public $id;
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
            [['id', 'code', 'type', 'value', 'min_order_value', 'max_discount', 'max_usage', 'starts_at', 'expires_at'], 'required'],
            [['id', 'max_usage', 'used_count', 'is_active'], 'integer'],
            [['value', 'min_order_value', 'max_discount'], 'number'],
            [['code'], 'string', 'max' => 50],
            [['type'], 'string', 'max' => 20],
            [['starts_at', 'expires_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['code'], 'unique', 'targetClass' => Coupons::class, 'targetAttribute' => 'code'],
        ];
    }
}
