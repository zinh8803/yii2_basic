<?php

use yii\db\Migration;

class m260520_033445_update_rating_table_reviews extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('reviews', 'is_approved', $this->boolean()->notNull()->defaultValue(true));

        $this->alterColumn('reviews', 'rating', $this->float()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('reviews', 'is_approved', $this->boolean()->notNull()->defaultValue(false));
        $this->alterColumn('reviews', 'rating', $this->integer()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260520_033445_update_rating_table_reviews cannot be reverted.\n";

        return false;
    }
    */
}
