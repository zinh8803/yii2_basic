<?php

use yii\db\Migration;

class m260515_030642_update_table_product_variant_status_to_is_active extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%product_variants}}', [
            'status' => 1
        ], [
            'status' => 'active'
        ]);

        $this->update('{{%product_variants}}', [
            'status' => 0
        ], [
            'status' => 'inactive'
        ]);

        $this->alterColumn(
            '{{%product_variants}}',
            'status',
            $this->boolean()->defaultValue(1)->notNull()
        );

        $this->renameColumn(
            '{{%product_variants}}',
            'status',
            'is_active'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn(
            '{{%product_variants}}',
            'is_active',
            'status'
        );

        $this->alterColumn(
            '{{%product_variants}}',
            'status',
            $this->string()->defaultValue('active')->notNull()
        );

        $this->update('{{%product_variants}}', [
            'status' => 'active'
        ], [
            'status' => 1
        ]);

        $this->update('{{%product_variants}}', [
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
        echo "m260515_030642_update_table_product_variant_status_to_is_active cannot be reverted.\n";

        return false;
    }
    */
}
