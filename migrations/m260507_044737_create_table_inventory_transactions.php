<?php

use yii\db\Migration;

class m260507_044737_create_table_inventory_transactions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("inventory_transactions", [
            "id" => $this->primaryKey(),
            'type' => $this->string(50)->notNull(),
            'warehouse_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'approved_by' => $this->integer()->null(),
            'status' => $this->string(50)->notNull()->defaultValue("pending"),
            'note' => $this->text()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey("fk_inventory_transactions_warehouse", "inventory_transactions", "warehouse_id", "warehouses", "id", "CASCADE", "CASCADE");
        $this->addForeignKey("fk_inventory_transactions_user", "inventory_transactions", "created_by", "users", "id", "CASCADE", "CASCADE");
        $this->addForeignKey("fk_inventory_transactions_approved", "inventory_transactions", "approved_by", "users", "id", "CASCADE", "CASCADE");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_inventory_transactions_approved", "inventory_transactions");
        $this->dropForeignKey("fk_inventory_transactions_user", "inventory_transactions");
        $this->dropForeignKey("fk_inventory_transactions_warehouse", "inventory_transactions");
        $this->dropTable('inventory_transactions');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_044737_create_table_inventory_transactions cannot be reverted.\n";

        return false;
    }
    */
}
