<?php

use yii\db\Migration;

class m260511_094145_update_table_coupons extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('coupons', 'update_at', 'updated_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('coupons', 'updated_at', 'update_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260511_094145_update_table_coupons cannot be reverted.\n";

        return false;
    }
    */
}
