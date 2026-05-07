<?php

use yii\db\Migration;

class m260506_044226_update_table_orders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("orders", "order_code", $this->string(255)->notNull()->after("id"));
        $this->addColumn("orders", "stacking_id", $this->string()->notNull()->after("order_code"));
        $this->addColumn("orders", "email", $this->string(255)->notNull()->after("user_id"));
        $this->addColumn("orders", "receiver_name", $this->string(255)->null()->after("email"));
        $this->addColumn("orders", "receiver_phone", $this->string(20)->notNull()->after("receiver_name"));
        $this->addColumn("orders", "receiver_address", $this->text()->after("receiver_phone"));
        $this->addColumn("orders", "note", $this->text()->after("receiver_address"));
        $this->addColumn("orders", "is_discounted", $this->boolean()->notNull()->defaultValue(false)->after("note"));
        $this->addColumn("orders", "shipping_fee", $this->decimal(10, 2)->notNull()->defaultValue(0)->after("total"));
        $this->addColumn("orders", "discount_amount", $this->decimal(10, 2)->notNull()->defaultValue(0)->after("shipping_fee"));
        $this->addColumn("orders", "payment_method", $this->integer()->null()->after("discount_amount"));
        $this->addColumn("orders", "payment_status", $this->string(50)->null()->after("payment_method")->defaultValue("pending"));
        $this->addColumn("orders", "created_at", $this->integer()->null()->after("discount_amount"));
        $this->addColumn("orders", "updated_at", $this->integer()->null()->after("created_at"));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("orders", "coupon_id");
        $this->dropColumn("orders", "discount_amount");
        $this->dropColumn("orders", "email");
        $this->dropColumn("orders", "receiver_phone");
        $this->dropColumn("orders", "receiver_address");
        $this->dropColumn("orders", "note");
        $this->dropColumn("orders", "is_discounted");
        $this->dropColumn("orders", "shipping_fee");
        $this->dropColumn("orders", "discount_amount");
        $this->dropColumn("orders", "payment_method");
        $this->dropColumn("orders", "payment_status");
        $this->dropColumn("orders", "created_at");
        $this->dropColumn("orders", "updated_at");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    public function down()
    {
        echo "m260506_044226_update_table_orders cannot be reverted.\n";

        return false;
    }
    */
}
