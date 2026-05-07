<?php

use yii\db\Migration;

class m260506_095158_create_table_payments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('payments', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'status' => $this->string(50)->notNull()->defaultValue("pending"),
            'amount' => $this->decimal(10, 2)->notNull(),
            'payment_method' => $this->string(50)->notNull(),
            'payment_status' => $this->string(50)->notNull()->defaultValue("pending"),
            'transaction_id' => $this->string(255)->null(),
            'gateway_response' => $this->text()->null(),
            'idempotency_key' => $this->string(255)->null(),
            'refunded_amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'refund_status' => $this->string(50)->notNull()->defaultValue("none"),
            'refund_ad' => $this->integer()->null(),
            'paid_at' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('payments');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_095158_update_table_payments cannot be reverted.\n";

        return false;
    }
    */
}
