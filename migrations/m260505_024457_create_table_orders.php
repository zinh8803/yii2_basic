<?php

use yii\db\Migration;

class m260505_024457_create_table_orders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('orders', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'total' => $this->decimal(10, 2)->notNull(),
            'status' => $this->string(255)->notNull(),
        ]);
        $this->addForeignKey(
            'fk_orders_users',
            'orders',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_orders_users', 'orders');
        $this->dropTable('orders');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260505_024457_create_table_orders cannot be reverted.\n";

        return false;
    }
    */
}
