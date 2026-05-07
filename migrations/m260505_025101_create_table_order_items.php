<?php

use yii\db\Migration;

class m260505_025101_create_table_order_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_items', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'quantity' => $this->integer()->notNull(),
            'price' => $this->decimal(10, 2)->notNull(),
        ]);

        $this->addForeignKey(
            'fk_order_items_orders',
            'order_items',
            'order_id',
            'orders',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_order_items_products',
            'order_items',
            'product_id',
            'products',
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
        $this->dropForeignKey('fk_order_items_orders', 'order_items');
        $this->dropTable('order_items');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260505_025101_create_table_order_items cannot be reverted.\n";

        return false;
    }
    */
}
