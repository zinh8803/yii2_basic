<?php

use yii\db\Migration;

class m260506_095035_create_table_reviews extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('reviews', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'rating' => $this->integer()->notNull(),
            'comment' => $this->text()->null(),
            'is_approved' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk_reviews_product',
            'reviews',
            'product_id',
            'products',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_reviews_user',
            'reviews',
            'user_id',
            'users',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_reviews_product', 'reviews');
        $this->dropForeignKey('fk_reviews_user', 'reviews');
        $this->dropTable('reviews');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_095035_update_table_reviews cannot be reverted.\n";

        return false;
    }
    */
}
