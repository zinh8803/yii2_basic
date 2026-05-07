<?php

use yii\db\Migration;

class m260507_045322_create_table_product_variants extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("product_variants", [
            "id" => $this->primaryKey(),
            "product_id" => $this->integer()->notNull(),
            "name" => $this->string(255)->notNull(),
            "sku" => $this->string(100)->unique(),
            "price" => $this->decimal(10, 2)->notNull(),
            "sale_price" => $this->decimal(10, 2)->null(),
            "cost_price" => $this->decimal(10, 2)->null(),
            "weight" => $this->decimal(10, 2)->null(),
            "status" => $this->string(50)->notNull()->defaultValue("active"),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_product_variants_product",
            "product_variants",
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
        $this->dropForeignKey("fk_product_variants_product", "product_variants");
        $this->dropTable("product_variants");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_045322_create_table_product_variants cannot be reverted.\n";

        return false;
    }
    */
}
