<?php

namespace app\models\forms\Order;

use yii\base\Model;

class UpdateStatusOrderForm extends Model
{
    public $id;
    public $status;
    public $payment_status;
    public function rules()
    {
        return [
            [['id', 'status'], 'required'],
            [['status'], 'string', 'max' => 255],
            [['payment_status'], 'string', 'max' => 255],
            [['id'], 'integer'],
        ];
    }
}
