<?php

use yii\db\Migration;

class m260514_094159_update_table_product_vartiants extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product_variants', 'stock', $this->integer(11)->notNull()->after('cost_price'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('product_variants', 'stock');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260514_094159_update_table_product_vartiants cannot be reverted.\n";

        return false;
    }
    */
}
