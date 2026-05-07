<?php

use yii\db\Migration;

class m260506_041820_create_table_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("roles", [
            "id" => $this->primaryKey(),
            "name" => $this->string(255)->notNull(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260506_041820_create_table_roles cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_041820_create_table_roles cannot be reverted.\n";

        return false;
    }
    */
}
