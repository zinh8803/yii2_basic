<?php

use yii\db\Migration;

class m260507_065957_create_table_resources extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("resources", [
            'id' => $this->primaryKey(),
            'file_id' => $this->integer()->notNull(),
            'resource_type' => $this->string(255)->notNull(),
            'resource_id' => $this->integer()->notNull(),
            'type' => $this->string(50)->notNull(),
            'title' => $this->string(255)->notNull(),
            'alt_text' => $this->string(255)->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_primary' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_resources_file",
            "resources",
            "file_id",
            "files",
            "id",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_resources_file", "resources");
        $this->dropTable("resources");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_065957_update_table_resources cannot be reverted.\n";

        return false;
    }
    */
}
