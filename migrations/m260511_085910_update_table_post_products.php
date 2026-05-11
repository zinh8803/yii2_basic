<?php

use yii\db\Migration;

class m260511_085910_update_table_post_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("post_products", "id", $this->primaryKey());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("post_products", "id");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260511_085910_update_table_post_products cannot be reverted.\n";

        return false;
    }
    */
}
