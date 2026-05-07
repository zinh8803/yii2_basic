<?php

use yii\db\Migration;

class m260506_043908_create_table_coupon_usages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("coupon_usages", [
            'id' => $this->primaryKey(),
            'coupon_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'order_id' => $this->integer()->notNull(),
            'used_at' => $this->integer()->notNull(),
            "discount_applied" => $this->decimal(10, 2)->notNull(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable("coupon_usages");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_043908_create_table_coupon_usages cannot be reverted.\n";

        return false;
    }
    */
}
