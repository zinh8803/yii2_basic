<?php

use yii\db\Migration;

class m260506_043521_create_table_coupons extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("coupons", [
            'id' => $this->primaryKey(),
            'code' => $this->string(50)->notNull()->unique(),
            'type' => $this->string(20)->notNull(),
            'value' => $this->decimal(10, 2)->notNull(),
            "min_order_value" => $this->decimal(10, 2)->notNull(),
            "max_discount" => $this->decimal(10, 2)->notNull(),
            "max_usage" => $this->integer()->notNull(),
            "used_count" => $this->integer()->notNull()->defaultValue(0),
            "starts_at" => $this->integer()->notNull(),
            "expires_at" => $this->integer()->notNull(),
            "is_active" => $this->boolean()->notNull()->defaultValue(true),
            "created_at" => $this->integer()->notNull(),
            "update_at" => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable("coupons");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {


    public function down()
    {
        echo "m260506_043521_create_table_coupons cannot be reverted.\n";

        return false;
    }
    */
}
