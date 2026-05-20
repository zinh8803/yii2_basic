<?php

use yii\db\Migration;

class m260519_090000_optimize_post_tag_product_relations extends Migration
{
    public function safeUp()
    {
        $this->dropIndexIfExists('tags', 'description');

        $this->createIndex(
            'uq_post_products_post_product',
            'post_products',
            ['post_id', 'product_id'],
            true
        );

        $this->createIndex(
            'idx_taggables_post_type',
            'taggables',
            ['post_id', 'type']
        );
        $this->createIndex(
            'uq_taggables_post_tag_type',
            'taggables',
            ['post_id', 'tag_id', 'type'],
            true
        );
    }

    public function safeDown()
    {
        $this->dropIndex('uq_taggables_post_tag_type', 'taggables');
        $this->dropIndex('idx_taggables_post_type', 'taggables');

        $this->dropIndex('uq_post_products_post_product', 'post_products');
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $indexExists = $this->db->createCommand(
            'SHOW INDEX FROM {{%' . $table . '}} WHERE Key_name = :index',
            [':index' => $index]
        )->queryOne();

        if ($indexExists !== false) {
            $this->dropIndex($index, $table);
        }
    }
}
