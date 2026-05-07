<?php

use yii\db\Migration;

class m260507_044104_create_table_refresh_tokens extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("refresh_tokens", [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token' => $this->string(255)->notNull(),
            'device_name' => $this->string(255)->null(),
            'ip_address' => $this->string(45)->null(),
            'user_agent' => $this->string(255)->null(),
            'expires_at' => $this->integer()->notNull(),
            'revoked_at' => $this->integer()->null(),
            'last_used_at' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey("fk_refresh_tokens_user", "refresh_tokens", "user_id", "users", "id", "CASCADE", "CASCADE");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_refresh_tokens_user", "refresh_tokens");
        $this->dropTable('refresh_tokens');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_044104_create_table_refresh_tokens cannot be reverted.\n";

        return false;
    }
    */
}
