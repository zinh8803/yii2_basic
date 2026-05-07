<?php

use yii\db\Migration;

class m260506_070758_update_table_coupon_usages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey("fk_coupon_usages_coupon", "coupon_usages", "coupon_id", "coupons", "id", "CASCADE");
        $this->addForeignKey("fk_coupon_usages_user", "coupon_usages", "user_id", "users", "id", "CASCADE");
        $this->addForeignKey("fk_coupon_usages_order", "coupon_usages", "order_id", "orders", "id", "CASCADE");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_coupon_usages_coupon", "coupon_usages");
        $this->dropForeignKey("fk_coupon_usages_user", "coupon_usages");
        $this->dropForeignKey("fk_coupon_usages_order", "coupon_usages");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_070758_update_table_coupon_usages cannot be reverted.\n";

        return false;
    }
    */
}
