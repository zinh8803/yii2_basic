<?php

use yii\db\Migration;

class m260506_064412_create_table_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("files", [
            "id" => $this->primaryKey(),
            "user_id" => $this->integer()->notNull(),
            "post_id" => $this->integer()->null(),
            "url" => $this->string(255)->notNull(),
            "original_name" => $this->string(255)->notNull(),
            "mime_type" => $this->string(255)->notNull(),
            "size_bytes" => $this->integer()->notNull(),
            "width" => $this->integer()->null(),
            "height" => $this->integer()->null(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_files_user",
            "files",
            "user_id",
            "users",
            "id",
            "CASCADE"
        );
        $this->addForeignKey(
            "fk_files_post",
            "files",
            "post_id",
            "posts",
            "id",
            "SET NULL"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_files_user", "files");
        $this->dropForeignKey("fk_files_post", "files");
        $this->dropTable("files");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_064412_create_table_files cannot be reverted.\n";

        return false;
    }
    */
}
