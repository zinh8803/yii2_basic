<?php

use yii\db\Migration;

class m260506_045326_update_table_categories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("categories", "parent_id", $this->integer()->null()->after("id"));
        $this->addForeignKey("fk_categories_parent", "categories", "parent_id", "categories", "id", "SET NULL", "CASCADE");
        $this->addColumn("categories", "slug", $this->string(255)->notNull()->after("name"));
        $this->addColumn("categories", "status", $this->boolean()->notNull()->defaultValue(true)->after("slug"));
        $this->addColumn("categories", "created_at", $this->integer()->null()->after("slug"));
        $this->addColumn("categories", "updated_at", $this->integer()->null()->after("created_at"));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_categories_parent", "categories");
        $this->dropColumn("categories", "parent_id");
        $this->dropColumn("categories", "slug");
        $this->dropColumn("categories", "created_at");
        $this->dropColumn("categories", "updated_at");
        $this->dropColumn("categories", "status");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_045326_update_table_categories cannot be reverted.\n";

        return false;
    }
    */
}
