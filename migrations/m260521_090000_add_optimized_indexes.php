<?php

use yii\db\Migration;

/**
 * Adds database indexes that match current ActiveRecord rules and query paths.
 */
class m260521_090000_add_optimized_indexes extends Migration
{
    public function safeUp()
    {
        $this->createIndexIfMissing('idx-products-status-created_at', '{{%products}}', ['status', 'created_at']);
        $this->createIndexIfMissing('idx-products-category_id-status', '{{%products}}', ['category_id', 'status']);
        $this->createIndexIfMissing('idx-products-brand_id-status', '{{%products}}', ['brand_id', 'status']);
        $this->createIndexIfMissing('uniq-products-name', '{{%products}}', 'name', true);
        $this->createIndexIfMissing('uniq-products-slug', '{{%products}}', 'slug', true);

        $this->createIndexIfMissing('idx-product_variants-product_id-is_active', '{{%product_variants}}', ['product_id', 'is_active']);
        $this->createIndexIfMissing('idx-product_variants-price', '{{%product_variants}}', 'price');

        $this->createIndexIfMissing('idx-resources-resource_type-resource_id', '{{%resources}}', ['resource_type', 'resource_id']);
        $this->createIndexIfMissing('idx-resources-primary_lookup', '{{%resources}}', ['resource_type', 'resource_id', 'type', 'is_primary']);

        $this->createIndexIfMissing('idx-files-created_at', '{{%files}}', 'created_at');
        $this->createIndexIfMissing('idx-files-user_id-created_at', '{{%files}}', ['user_id', 'created_at']);

        $this->createIndexIfMissing('idx-orders-status-created_at', '{{%orders}}', ['status', 'created_at']);
        $this->createIndexIfMissing('idx-orders-user_id-status', '{{%orders}}', ['user_id', 'status']);
        $this->createIndexIfMissing('uniq-orders-order_code', '{{%orders}}', 'order_code', true);
        $this->createIndexIfMissing('uniq-orders-stacking_id', '{{%orders}}', 'stacking_id', true);

        $this->createIndexIfMissing('uniq-brands-name', '{{%brands}}', 'name', true);
        $this->createIndexIfMissing('uniq-brands-slug', '{{%brands}}', 'slug', true);
        $this->createIndexIfMissing('idx-brands-status', '{{%brands}}', 'status');

        $this->createIndexIfMissing('uniq-categories-name', '{{%categories}}', 'name', true);
        $this->createIndexIfMissing('uniq-categories-slug', '{{%categories}}', 'slug', true);
        $this->createIndexIfMissing('idx-categories-parent_id-status', '{{%categories}}', ['parent_id', 'status']);

        $this->createIndexIfMissing('idx-posts-status-published_at', '{{%posts}}', ['status', 'published_at']);
        $this->createIndexIfMissing('idx-posts-user_id-status', '{{%posts}}', ['user_id', 'status']);
        $this->createIndexIfMissing('idx-posts-slug', '{{%posts}}', 'slug');

        $this->createIndexIfMissing('idx-product_attributes-product_id-is_variant', '{{%product_attributes}}', ['product_id', 'is_variant']);
        $this->createIndexIfMissing('idx-attribute_values-attribute_id-sort_order', '{{%attribute_values}}', ['attribute_id', 'sort_order']);

        $this->createIndexIfMissing('idx-reviews-product_id-is_approved', '{{%reviews}}', ['product_id', 'is_approved']);
        $this->createIndexIfMissing('idx-coupons-is_active-code', '{{%coupons}}', ['is_active', 'code']);
    }

    public function safeDown()
    {
        $this->ensureForeignKeyIndexes();

        $this->dropIndexIfExists('idx-coupons-is_active-code', '{{%coupons}}');
        $this->dropIndexIfExists('idx-reviews-product_id-is_approved', '{{%reviews}}');
        $this->dropIndexIfExists('idx-attribute_values-attribute_id-sort_order', '{{%attribute_values}}');
        $this->dropIndexIfExists('idx-product_attributes-product_id-is_variant', '{{%product_attributes}}');
        $this->dropIndexIfExists('idx-posts-slug', '{{%posts}}');
        $this->dropIndexIfExists('idx-posts-user_id-status', '{{%posts}}');
        $this->dropIndexIfExists('idx-posts-status-published_at', '{{%posts}}');
        $this->dropIndexIfExists('idx-categories-parent_id-status', '{{%categories}}');
        $this->dropIndexIfExists('uniq-categories-slug', '{{%categories}}');
        $this->dropIndexIfExists('uniq-categories-name', '{{%categories}}');
        $this->dropIndexIfExists('idx-brands-status', '{{%brands}}');
        $this->dropIndexIfExists('uniq-brands-slug', '{{%brands}}');
        $this->dropIndexIfExists('uniq-brands-name', '{{%brands}}');
        $this->dropIndexIfExists('uniq-orders-stacking_id', '{{%orders}}');
        $this->dropIndexIfExists('uniq-orders-order_code', '{{%orders}}');
        $this->dropIndexIfExists('idx-orders-user_id-status', '{{%orders}}');
        $this->dropIndexIfExists('idx-orders-status-created_at', '{{%orders}}');
        $this->dropIndexIfExists('idx-files-user_id-created_at', '{{%files}}');
        $this->dropIndexIfExists('idx-files-created_at', '{{%files}}');
        $this->dropIndexIfExists('idx-resources-primary_lookup', '{{%resources}}');
        $this->dropIndexIfExists('idx-resources-resource_type-resource_id', '{{%resources}}');
        $this->dropIndexIfExists('idx-product_variants-price', '{{%product_variants}}');
        $this->dropIndexIfExists('idx-product_variants-product_id-is_active', '{{%product_variants}}');
        $this->dropIndexIfExists('uniq-products-slug', '{{%products}}');
        $this->dropIndexIfExists('uniq-products-name', '{{%products}}');
        $this->dropIndexIfExists('idx-products-brand_id-status', '{{%products}}');
        $this->dropIndexIfExists('idx-products-category_id-status', '{{%products}}');
        $this->dropIndexIfExists('idx-products-status-created_at', '{{%products}}');
    }

    private function createIndexIfMissing(string $name, string $table, string|array $columns, bool $unique = false): void
    {
        if (!$this->indexExists($table, $name)) {
            $this->createIndex($name, $table, $columns, $unique);
        }
    }

    private function dropIndexIfExists(string $name, string $table): void
    {
        if ($this->indexExists($table, $name)) {
            $this->dropIndex($name, $table);
        }
    }

    private function ensureForeignKeyIndexes(): void
    {
        $this->createIndexIfMissing('fk_reviews_product', '{{%reviews}}', 'product_id');
        $this->createIndexIfMissing('fk_attribute_values_attribute', '{{%attribute_values}}', 'attribute_id');
        $this->createIndexIfMissing('fk_product_attributes_product', '{{%product_attributes}}', 'product_id');
        $this->createIndexIfMissing('fk_categories_parent', '{{%categories}}', 'parent_id');
        $this->createIndexIfMissing('fk_orders_users', '{{%orders}}', 'user_id');
        $this->createIndexIfMissing('fk_files_user', '{{%files}}', 'user_id');
        $this->createIndexIfMissing('fk_product_variants_product', '{{%product_variants}}', 'product_id');
        $this->createIndexIfMissing('fk_product_category', '{{%products}}', 'category_id');
        $this->createIndexIfMissing('fk_products_brand', '{{%products}}', 'brand_id');
        $this->createIndexIfMissing('fk_posts_user', '{{%posts}}', 'user_id');
    }

    private function indexExists(string $table, string $name): bool
    {
        $rawTableName = $this->db->schema->getRawTableName($table);

        return (bool) $this->db->createCommand('SHOW INDEX FROM ' . $this->db->quoteTableName($rawTableName) . ' WHERE Key_name = :name', [
            ':name' => $name,
        ])->queryScalar();
    }
}
