<?php

use yii\db\Migration;

class m260506_065052_create_table_taggables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("taggables", [
            "id" => $this->primaryKey(),
            "tag_id" => $this->integer()->notNull(),
            "post_id" => $this->integer()->null(),
            "type" => $this->string(255)->notNull(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_taggables_tag",
            "taggables",
            "tag_id",
            "tags",
            "id",
            "CASCADE"
        );
        $this->addForeignKey(
            "fk_taggables_post",
            "taggables",
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
        $this->dropForeignKey("fk_taggables_tag", "taggables");
        $this->dropForeignKey("fk_taggables_post", "taggables");
        $this->dropTable("taggables");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_065052_create_table_taggables cannot be reverted.\n";

        return false;
    }
    */
}
