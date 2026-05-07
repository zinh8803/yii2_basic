<?php

use yii\db\Migration;

class m260507_045556_create_table_brands extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("brands", [
            "id" => $this->primaryKey(),
            "name" => $this->string(255)->notNull(),
            "slug" => $this->string(255)->notNull(),
            "status" => $this->string(50)->notNull()->defaultValue("active"),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable("brands");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_045556_create_table_brands cannot be reverted.\n";

        return false;
    }
    */
}
