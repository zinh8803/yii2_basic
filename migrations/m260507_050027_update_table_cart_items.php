<?php

use yii\db\Migration;

class m260507_050027_update_table_cart_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("cart_items", "product_variant_id", $this->integer()->null()->after("product_id"));
        $this->addForeignKey("fk_cart_items_product_variant", "cart_items", "product_variant_id", "product_variants", "id", "SET NULL", "CASCADE");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_cart_items_product_variant", "cart_items");
        $this->dropColumn("cart_items", "product_variant_id");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_050027_update_table_cart_items cannot be reverted.\n";

        return false;
    }
    */
}
