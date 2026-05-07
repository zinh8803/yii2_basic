<?php

use yii\db\Migration;

class m260507_045148_update_table_product_attributes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn("product_attributes", "value");
        $this->addColumn("product_attributes", "type", $this->string(50)->notNull()->defaultValue("text")->after("name"));
        $this->addColumn("product_attributes", "slug", $this->string(255)->after("type"));
        $this->addColumn("product_attributes", "is_variant", $this->boolean()->defaultValue(false)->after("slug"));
        $this->addColumn("product_attributes", "sort_order", $this->integer()->notNull()->defaultValue(0)->after("is_variant"));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("product_attributes", "type");
        $this->dropColumn("product_attributes", "slug");
        $this->dropColumn("product_attributes", "is_variant");
        $this->dropColumn("product_attributes", "sort_order");
        $this->addColumn("product_attributes", "value", $this->string(255)->notNull()->after("name"));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_050311_update_table_product_attributes cannot be reverted.\n";

        return false;
    }
    */
}
