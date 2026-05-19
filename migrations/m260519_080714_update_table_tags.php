<?php

use yii\db\Migration;

class m260519_080714_update_table_tags extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
      $this->dropIndex('description', 'tags');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createIndex('idx-tags-description', 'tags', 'description');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260519_080714_update_table_tags cannot be reverted.\n";

        return false;
    }
    */
}
