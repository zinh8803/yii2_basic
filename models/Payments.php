<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payments".
 *
 * @property int $id
 * @property int $order_id
 * @property string $status
 * @property float $amount
 * @property string $payment_method
 * @property string $payment_status
 * @property string|null $transaction_id
 * @property string|null $gateway_response
 * @property string|null $idempotency_key
 * @property float $refunded_amount
 * @property string $refund_status
 * @property int|null $refund_ad
 * @property int|null $paid_at
 * @property int $created_at
 * @property int $updated_at
 */
class Payments extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['transaction_id', 'gateway_response', 'idempotency_key', 'refund_ad', 'paid_at'], 'default', 'value' => null],
            [['payment_status'], 'default', 'value' => 'pending'],
            [['refunded_amount'], 'default', 'value' => 0.00],
            [['refund_status'], 'default', 'value' => 'none'],
            [['order_id', 'amount', 'payment_method', 'created_at', 'updated_at'], 'required'],
            [['order_id', 'refund_ad', 'paid_at', 'created_at', 'updated_at'], 'integer'],
            [['amount', 'refunded_amount'], 'number'],
            [['gateway_response'], 'string'],
            [['status', 'payment_method', 'payment_status', 'refund_status'], 'string', 'max' => 50],
            [['transaction_id', 'idempotency_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'status' => 'Status',
            'amount' => 'Amount',
            'payment_method' => 'Payment Method',
            'payment_status' => 'Payment Status',
            'transaction_id' => 'Transaction ID',
            'gateway_response' => 'Gateway Response',
            'idempotency_key' => 'Idempotency Key',
            'refunded_amount' => 'Refunded Amount',
            'refund_status' => 'Refund Status',
            'refund_ad' => 'Refund Ad',
            'paid_at' => 'Paid At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
