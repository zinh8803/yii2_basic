<?php

use yii\db\Migration;

class m260507_043756_create_table_otp_emails extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("otp_emails", [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->null(),
            'email' => $this->string(255)->notNull(),
            'otp_code' => $this->string(10)->notNull(),
            'type' => $this->string(50)->notNull(),
            'expired_at' => $this->integer()->notNull(),
            'verified_at' => $this->integer()->notNull(),
            'attempts' => $this->integer()->notNull()->defaultValue(0),
            'max_attempts' => $this->integer()->notNull()->defaultValue(5),
            'ip_address' => $this->string(45)->null(),
            'user_agent' => $this->string(255)->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey("fk_otp_emails_user", "otp_emails", "user_id", "users", "id", "SET NULL", "CASCADE");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_otp_emails_user", "otp_emails");
        $this->dropTable('otp_emails');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_043756_create_table_otp_emails cannot be reverted.\n";

        return false;
    }
    */
}
