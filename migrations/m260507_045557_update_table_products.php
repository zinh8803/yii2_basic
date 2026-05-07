<?php

use yii\db\Migration;

class m260507_045557_update_table_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn("products", "price");
        $this->dropColumn("products", "stock");
        $this->addColumn("products", "brand_id", $this->integer()->null()->after("category_id"));
        $this->addForeignKey("fk_products_brand", "products", "brand_id", "brands", "id", "SET NULL", "CASCADE");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_products_brand", "products");
        $this->dropColumn("products", "brand_id");
        $this->addColumn("products", "price", $this->decimal(10, 2)->defaultValue(0));
        $this->addColumn("products", "stock", $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_045557_update_table_products cannot be reverted.\n";

        return false;
    }
    */
}
