<?php

use yii\db\Migration;

class m260507_045149_create_table_attribute_values extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("attribute_values", [
            "id" => $this->primaryKey(),
            "attribute_id" => $this->integer()->notNull(),
            "value" => $this->string(255)->notNull(),
            "slug" => $this->string(255)->notNull(),
            "color_hex" => $this->string(10)->null(),
            "sort_order" => $this->integer()->notNull()->defaultValue(0),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_attribute_values_attribute",
            "attribute_values",
            "attribute_id",
            "product_attributes",
            "id",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_attribute_values_attribute", "attribute_values");
        $this->dropTable("attribute_values");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_045149_create_table_attribute_values cannot be reverted.\n";

        return false;
    }
    */
}
