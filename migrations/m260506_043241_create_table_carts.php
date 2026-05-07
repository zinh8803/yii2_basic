<?php

use yii\db\Migration;

class m260506_043241_create_table_carts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("carts", [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            "total" => $this->decimal(10, 2)->notNull(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            "fk_carts_user",
            "carts",
            "user_id",
            "users",
            "id",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_carts_user", "carts");
        $this->dropTable("carts");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_043241_create_table_carts cannot be reverted.\n";

        return false;
    }
    */
}
