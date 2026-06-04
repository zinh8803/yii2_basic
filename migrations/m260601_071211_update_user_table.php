<?php

use yii\db\Migration;

class m260601_071211_update_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%users}}', 'auth_key', $this->string(64)->unique()->after('password'));
        $this->createIndex('idx-users-auth_key', '{{%users}}', 'auth_key');

        $this->addColumn('{{%users}}', 'access_token', $this->string(64)->unique()->after('auth_key'));
        $this->createIndex('idx-users-access_token', '{{%users}}', 'access_token');

        $this->dropForeignKey('fk_user_role', '{{%users}}');
        $this->dropColumn('{{%users}}', 'role_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-users-auth_key', '{{%users}}');
        $this->dropColumn('{{%users}}', 'auth_key');

        $this->dropIndex('idx-users-access_token', '{{%users}}');
        $this->dropColumn('{{%users}}', 'access_token');

        $this->addColumn('{{%users}}', 'role_id', $this->integer()->after('id'));
        $this->addForeignKey('fk_user_role', '{{%users}}', 'role_id', '{{%roles}}', 'id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260601_071211_update_user_table cannot be reverted.\n";

        return false;
    }
    */
}
