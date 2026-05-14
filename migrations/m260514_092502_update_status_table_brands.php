<?php

use yii\db\Migration;

class m260514_092502_update_status_table_brands extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%brands}}', [
            'status' => 1
        ], [
            'status' => 'active'
        ]);

        $this->update('{{%brands}}', [
            'status' => 0
        ], [
            'status' => 'inactive'
        ]);

        $this->alterColumn(
            '{{%brands}}',
            'status',
            $this->integer()->defaultValue(1)->notNull()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(
            '{{%brands}}',
            'status',
            $this->string()->defaultValue('active')->notNull()
        );

        $this->update('{{%brands}}', [
            'status' => 'active'
        ], [
            'status' => 1
        ]);

        $this->update('{{%brands}}', [
            'status' => 'inactive'
        ], [
            'status' => 0
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260514_092502_update_status_table_brands cannot be reverted.\n";

        return false;
    }
    */
}
