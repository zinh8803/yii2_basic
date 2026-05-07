<?php

use yii\db\Migration;

class m260506_045045_update_table_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("products", "slug", $this->string(255)->notNull()->after("stock"));
        $this->addColumn("products", "description", $this->string(255)->after("slug"));
        $this->addColumn("products", "status", $this->boolean()->notNull()->defaultValue(true)->after("description"));
        $this->addColumn("products", "created_at", $this->integer()->null()->after("status"));
        $this->addColumn("products", "updated_at", $this->integer()->null()->after("created_at"));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("products", "slug");
        $this->dropColumn("products", "description");
        $this->dropColumn("products", "status");
        $this->dropColumn("products", "created_at");
        $this->dropColumn("products", "updated_at");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260506_045045_update_table_products cannot be reverted.\n";

        return false;
    }
    */
}
