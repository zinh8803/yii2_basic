<?php

use yii\db\Migration;

class m260505_025851_add_stock_to_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("products", "stock", $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("products", "stock");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260505_025851_add_stock_to_products_table cannot be reverted.\n";

        return false;
    }
    */
}
