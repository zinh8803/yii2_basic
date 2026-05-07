<?php

use yii\db\Migration;

class m260507_065459_update_table_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey("fk_files_post", "files");
        $this->dropColumn("files", "post_id");
        $this->addColumn("files", "disk", $this->string(50)->notNull()->after("user_id"));
        $this->addColumn("files", "path", $this->string(255)->notNull()->after("disk"));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn("files", "post_id", $this->integer()->null()->after("user_id"));
        $this->addForeignKey("fk_files_post", "files", "post_id", "posts", "id", "SET NULL", "CASCADE");

        $this->dropColumn("files", "disk");
        $this->dropColumn("files", "path");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260507_065459_update_table_files cannot be reverted.\n";

        return false;
    }
    */
}
