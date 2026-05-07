<?php

use yii\db\Migration;

class m260506_042656_create_table_user_addesses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("user_addresses", [
            "id" => $this->primaryKey(),
            "user_id" => $this->integer()->notNull(),
            "city" => $this->string(100)->notNull(),
            "ward" => $this->string(100)->notNull(),
            "detail_address" => $this->string(255)->notNull(),
            "phone_number" => $this->string(20)->notNull(),
            "name_address" => $this->string(255)->notNull(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            "fk_user_addresses_user",
            "user_addresses",
            "user_id",
            "users",
            "id",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable("user_addresses");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_042656_create_table_user_addesses cannot be reverted.\n";

        return false;
    }
    */
}
