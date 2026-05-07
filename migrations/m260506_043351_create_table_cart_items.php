<?php

use yii\db\Migration;

class m260506_043351_create_table_cart_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("cart_items", [
            'id' => $this->primaryKey(),
            'cart_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull(),
            'price' => $this->decimal(10, 2)->notNull(),
        ]);
        $this->addForeignKey(
            "fk_cart_items_cart",
            "cart_items",
            "cart_id",
            "carts",
            "id",
            "CASCADE"
        );
        $this->addForeignKey(
            "fk_cart_items_product",
            "cart_items",
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
        $this->dropForeignKey("fk_cart_items_cart", "cart_items");
        $this->dropForeignKey("fk_cart_items_product", "cart_items");
        $this->dropTable("cart_items");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_043351_create_table_cart_items cannot be reverted.\n";

        return false;
    }
    */
}
