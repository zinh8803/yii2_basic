<?php

use yii\db\Migration;

class m260506_064252_create_table_product_post_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("post_products", [
            "post_id" => $this->integer()->notNull(),
            "product_id" => $this->integer()->notNull(),
            "sort_order" => $this->integer()->notNull()->defaultValue(0),
            "note" => $this->string(255)->null(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_post_products_post",
            "post_products",
            "post_id",
            "posts",
            "id",
            "CASCADE"
        );
        $this->addForeignKey(
            "fk_post_products_product",
            "post_products",
            "product_id",
            "products",
            "id",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_post_products_post", "post_products");
        $this->dropForeignKey("fk_post_products_product", "post_products");
        $this->dropTable("post_products");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_064252_create_table_product_post_products cannot be reverted.\n";

        return false;
    }
    */
}
