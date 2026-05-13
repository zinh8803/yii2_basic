<?php

use yii\db\Migration;

class m260513_071529_update_order_id_table_payments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey('fk_payments_order_id', 'payments', 'order_id', 'orders', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_payments_order_id', 'payments');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260513_071529_update_order_id_table_payments cannot be reverted.\n";

        return false;
    }
    */
}
