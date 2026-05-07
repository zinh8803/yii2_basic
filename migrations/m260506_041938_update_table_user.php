<?php

use yii\db\Migration;

class m260506_041938_update_table_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn("users", "address");
        $this->addColumn("users", "phone_number", $this->string(255)->notNull()->after("password"));
        $this->addColumn("users", "is_active", $this->boolean()->notNull()->after("phone_number")->defaultValue(1));
        $this->addColumn("users", "role_id", $this->integer()->notNull()->after("id"));
        $this->addForeignKey("fk_user_role", "users", "role_id", "roles", "id", "CASCADE");
        $this->addColumn("users", "created_at", $this->integer()->notNull()->after("phone_number"));
        $this->addColumn("users", "updated_at", $this->integer()->notNull()->after("created_at"));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn("users", "address", $this->string(255)->notNull()->after("password"));
        $this->dropForeignKey("fk_user_role", "users");
        $this->dropColumn("users", "role_id");
        $this->dropColumn("users", "phone_number");
        $this->dropColumn("users", "created_at");
        $this->dropColumn("users", "updated_at");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_041938_update_table_user cannot be reverted.\n";

        return false;
    }
    */
}
