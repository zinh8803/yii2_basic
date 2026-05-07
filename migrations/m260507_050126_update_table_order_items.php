<?php

use yii\db\Migration;

class m260507_050126_update_table_order_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn("order_items", "name");
        $this->addColumn("order_items", "variant_id", $this->integer()->after("product_id"));
        $this->addColumn("order_items", "product_name", $this->string()->notNull()->after("variant_id"));
        $this->addColumn("order_items", "variant_name", $this->string(100)->after("product_name"));
        $this->addColumn("order_items", "image_url", $this->string(255)->after("variant_name"));
        $this->addColumn("order_items", "sku", $this->string(100)->after("image_url"));

        $this->addForeignKey(
            "fk_order_items_variant",
            "order_items",
            "variant_id",
            "product_variants",
            "id",
            "CASCADE"
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_order_items_variant", "order_items");
        $this->dropColumn("order_items", "variant_id");
        $this->dropColumn("order_items", "product_name");
        $this->dropColumn("order_items", "variant_name");
        $this->dropColumn("order_items", "image_url");
        $this->dropColumn("order_items", "sku");
        $this->addColumn("order_items", "name", $this->string(255)->notNull()->after("product_id"));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_050126_update_table_order_items cannot be reverted.\n";

        return false;
    }
    */
}
