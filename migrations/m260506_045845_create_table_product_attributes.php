<?php

use yii\db\Migration;

class m260506_045845_create_table_product_attributes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("product_attributes", [
            "id" => $this->primaryKey(),
            "product_id" => $this->integer()->notNull(),
            "name" => $this->string(255)->notNull(),
            "value" => $this->string(255)->notNull(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_product_attributes_product",
            "product_attributes",
            "product_id",
            "products",
            "id",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_product_attributes_product", "product_attributes");
        $this->dropTable("product_attributes");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_045845_create_table_product_attributes cannot be reverted.\n";

        return false;
    }
    */
}
