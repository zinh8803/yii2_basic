<?php

use yii\db\Migration;

class m260604_041407_add_softDelete_to_all_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tables = [
            '{{%users}}',
            '{{%posts}}',
            '{{%reviews}}',
            '{{%categories}}',
            '{{%brands}}',
            '{{%tags}}',
            '{{%products}}',
            '{{%product_variants}}',
            '{{%product_attributes}}',
            '{{%attribute_values}}',
            '{{%orders}}',
            '{{%order_items}}',
            '{{%carts}}',
            '{{%cart_items}}',
            '{{%files}}',
            '{{%resources}}',
        ];

        foreach ($tables as $table) {
            $schema = Yii::$app->db->schema->getTableSchema($table, true);

            if ($schema === null || !isset($schema->columns['updated_at'])) {
                echo "Skip {$table}: missing updated_at\n";
                continue;
            }

            if (!isset($schema->columns['deleted_at'])) {
                $this->addColumn($table, 'deleted_at', $this->integer()->null()->after('updated_at'));
                $this->createIndex($this->indexName($table, 'deleted_at'), $table, 'deleted_at');
            }

            $schema = Yii::$app->db->schema->getTableSchema($table, true);

            if (!isset($schema->columns['is_deleted'])) {
                $this->addColumn($table, 'is_deleted', $this->boolean()->notNull()->defaultValue(false)->after('deleted_at'));
                $this->createIndex($this->indexName($table, 'is_deleted'), $table, 'is_deleted');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tables =
            [
                '{{%users}}',
                '{{%posts}}',
                '{{%reviews}}',
                '{{%categories}}',
                '{{%brands}}',
                '{{%tags}}',
                '{{%products}}',
                '{{%product_variants}}',
                '{{%product_attributes}}',
                '{{%attribute_values}}',
                '{{%orders}}',
                '{{%order_items}}',
                '{{%carts}}',
                '{{%cart_items}}',
                '{{%files}}',
                '{{%resources}}',
            ];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($tables as $table) {
                $this->dropIndex($this->indexName($table, 'deleted_at'), $table);
                $this->dropColumn($table, 'deleted_at');
                $this->dropIndex($this->indexName($table, 'is_deleted'), $table);
                $this->dropColumn($table, 'is_deleted');
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $exception;
        }
    }

    private function tableNameOnly(string $table): string
    {
        return str_replace(['{{%', '}}'], '', $table);
    }

    private function indexName(string $table, string $column): string
    {
        return 'idx_' . $this->tableNameOnly($table) . '_' . $column;
    }
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260604_041407_add_softDelete_to_all_table cannot be reverted.\n";

        return false;
    }
    */
}
