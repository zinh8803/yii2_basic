<?php

use yii\db\Migration;

class m260507_044439_create_table_warehouse_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("warehouse_users", [
            'id' => $this->primaryKey(),
            'warehouse_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'role' => $this->string(50)->notNull()->defaultValue("staff"),
            'can_import' => $this->boolean()->notNull()->defaultValue(false),
            'can_export' => $this->boolean()->notNull()->defaultValue(false),
            'can_transfer' => $this->boolean()->notNull()->defaultValue(false),
            'can_adjust_stock' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey("fk_warehouse_users_warehouse", "warehouse_users", "warehouse_id", "warehouses", "id", "CASCADE", "CASCADE");
        $this->addForeignKey("fk_warehouse_users_user", "warehouse_users", "user_id", "users", "id", "CASCADE", "CASCADE");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_warehouse_users_user", "warehouse_users");
        $this->dropForeignKey("fk_warehouse_users_warehouse", "warehouse_users");
        $this->dropTable('warehouse_users');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_044439_create_table_warehouse_users cannot be reverted.\n";

        return false;
    }
    */
}
