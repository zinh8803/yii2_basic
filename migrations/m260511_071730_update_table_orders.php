<?php

use yii\db\Migration;

class m260511_071730_update_table_orders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('orders', 'payment_method', $this->string()->defaultValue('cod'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('orders', 'payment_method', $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260511_071730_update_table_orders cannot be reverted.\n";

        return false;
    }
    */
}
