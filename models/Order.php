<?php

namespace app\models;

use app\behaviors\Timestamp;
use Yii;
class Order extends base\Order
{
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
