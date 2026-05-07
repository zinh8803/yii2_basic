<?php

use yii\db\Migration;

class m260507_065040_drop_table_product_images extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey("fk_product_images_product", "product_images");
        $this->dropForeignKey("fk_product_images_file", "product_images");
        $this->dropTable("product_images");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable("product_images", [
            "id" => $this->primaryKey(),
            "product_id" => $this->integer()->notNull(),
            "file_id" => $this->integer()->notNull(),
            "url" => $this->string(255)->notNull(),
            "is_primary" => $this->boolean()->notNull()->defaultValue(false),
            "sort_order" => $this->integer()->notNull()->defaultValue(0),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_product_images_product",
            "product_images",
            "product_id",
            "products",
            "id",
            "CASCADE"
        );
        $this->addForeignKey(
            "fk_product_images_file",
            "product_images",
            "file_id",
            "files",
            "id",
            "CASCADE"
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_065040_drop_table_product_images cannot be reverted.\n";

        return false;
    }
    */
}
