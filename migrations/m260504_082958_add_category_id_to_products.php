<?php

use yii\db\Migration;

class m260504_082958_add_category_id_to_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('products', 'category_id', $this->integer());
        $this->addForeignKey(
            'fk_product_category',
            'products',
            'category_id',
            'categories',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_product_category', 'products');
        $this->dropColumn('products', 'category_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260504_082958_add_category_id_to_product cannot be reverted.\n";

        return false;
    }
    */
}
