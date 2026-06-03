<?php

namespace app\models\forms\Coupon;

use app\models\Coupon;

class CouponForm extends Coupon
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_CREATE => ['code', 'type', 'value', 'min_order_value', 'max_discount', 'max_usage', 'is_active', 'starts_at', 'expires_at'],
            self::SCENARIO_UPDATE => ['code', 'type', 'value', 'min_order_value', 'max_discount', 'max_usage', 'is_active', 'starts_at', 'expires_at'],
        ]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['code', 'type', 'value', 'min_order_value', 'max_discount', 'max_usage', 'starts_at', 'expires_at'], 'required', 'on' => self::SCENARIO_CREATE],

            [['starts_at'], 'validateStartDate', 'on' => self::SCENARIO_CREATE],
            [['expires_at'], 'validateEndDate'],

            [['code'], 'unique', 'targetClass' => Coupon::class, 'targetAttribute' => 'code', 'on' => self::SCENARIO_CREATE],
            [['code'], 'unique', 'targetClass' => Coupon::class, 'targetAttribute' => 'code', 'filter' => ['not', ['id' => $this->id]], 'on' => self::SCENARIO_UPDATE],
        ]);
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if (!empty($this->starts_at) && !is_numeric($this->starts_at)) {
            $this->starts_at = strtotime($this->starts_at);
        }

        if (!empty($this->expires_at) && !is_numeric($this->expires_at)) {
            $this->expires_at = strtotime($this->expires_at);
        }

        return true;
    }

    public function validateStartDate($attribute)
    {
        $today = strtotime(date('Y-m-d'));

        if ($this->$attribute < $today) {
            $this->addError(
                $attribute,
                'Start date cannot be earlier than today.'
            );
        }
    }

    public function validateEndDate($attribute)
    {
        if (empty($this->expires_at)) {
            return;
        }

        if ($this->scenario === self::SCENARIO_CREATE) {

            if ($this->expires_at < $this->starts_at) {
                $this->addError(
                    $attribute,
                    'End date must be greater than or equal to start date.'
                );
            }

            return;
        }

        if ($this->scenario === self::SCENARIO_UPDATE) {

            $coupon = Coupon::findOne($this->id);

            if (
                $coupon &&
                $this->expires_at < $coupon->starts_at
            ) {
                $this->addError(
                    $attribute,
                    'End date cannot be earlier than start date.'
                );
            }
        }
    }
}
