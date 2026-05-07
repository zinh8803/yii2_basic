<?php

use yii\db\Migration;

class m260506_064002_create_table_product_posts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("posts", [
            "id" => $this->primaryKey(),
            "user_id" => $this->integer()->notNull(),
            "title" => $this->string(255)->notNull(),
            "slug" => $this->string(255)->notNull(),
            "excerpt" => $this->text()->notNull(),
            "content" => $this->text()->notNull(),
            "status" => $this->string(50)->notNull()->defaultValue("draft"),
            "post_style" => $this->string(50)->notNull()->defaultValue("standard"),
            "meta_title" => $this->string(255)->null(),
            "meta_description" => $this->string(255)->null(),
            "published_at" => $this->integer()->null(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_posts_user",
            "posts",
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
        $this->dropForeignKey("fk_posts_user", "posts");
        $this->dropTable("posts");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_064002_create_table_product_posts cannot be reverted.\n";

        return false;
    }
    */
}
