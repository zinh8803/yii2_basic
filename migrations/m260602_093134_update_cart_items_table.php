<?php

use yii\db\Migration;

class m260602_093134_update_cart_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('cart_items', 'created_at', $this->integer()->notNull()->defaultValue(0)->after('price'));
        $this->addColumn('cart_items', 'updated_at', $this->integer()->notNull()->defaultValue(0)->after('created_at'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260602_093134_update_cart_items_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260602_093134_update_cart_items_table cannot be reverted.\n";

        return false;
    }
    */
}
