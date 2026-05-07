<?php

use yii\db\Migration;

class m260507_044259_create_table_warehouses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("warehouses", [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'city' => $this->text()->null(),
            'ward' => $this->string(255)->notNull(),
            'detail' => $this->text()->null(),
            'status' => $this->string(50)->notNull()->defaultValue("active"),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('warehouses');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_044259_create_table_warehouses cannot be reverted.\n";

        return false;
    }
    */
}
