<?php

use yii\db\Migration;

class m260521_074753_update_table_carts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('carts', 'total', $this->decimal(15, 2)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('carts', 'total', $this->decimal(10, 2)->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260521_074753_update_table_carts cannot be reverted.\n";

        return false;
    }
    */
}
