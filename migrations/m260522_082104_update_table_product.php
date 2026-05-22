<?php

use yii\db\Migration;

class m260522_082104_update_table_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('products', 'rating_avg', $this->float()->null()->after('status'));
        $this->addColumn('products', 'rating_count', $this->integer()->null()->after('rating_avg'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('products', 'rating_avg');
        $this->dropColumn('products', 'rating_count');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260522_082104_update_table_product cannot be reverted.\n";

        return false;
    }
    */
}
