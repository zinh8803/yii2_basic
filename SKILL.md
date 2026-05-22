---
name: php-pro
description: "Write idiomatic PHP 8.2+ and Yii2 code focusing on performance, memory efficiency, database optimization, migration indexes, and security."
risk: unknown
source: community
date_added: "2026-02-27"
---

## Use this skill when

- Working on PHP or Yii2 framework tasks or workflows
- Optimizing Yii2 ActiveRecord queries, database indexes, migrations, or API performance
- Reviewing migrations to check whether indexes, foreign keys, and constraints are optimized
- Needing guidance, performance optimization, or verification checklists for Yii2 application structure

## Do not use this skill when

- The task is unrelated to PHP/Yii2
- The task is frontend-only
- The task requires database-specific tuning without access to schema, query, or migration files

## Instructions

- Clarify goals, database engine, table structure, query patterns, and required inputs before coding.
- Review existing migrations before creating new indexes.
- Check whether indexes already exist before suggesting or creating new ones.
- Optimize based on real query patterns: `WHERE`, `JOIN`, `ORDER BY`, `GROUP BY`, pagination, and relation loading.
- Apply Yii2 design patterns: ActiveRecord, Query Builder, Components, Behaviors, SearchModel, and ActiveDataProvider.
- Prefer Yii2 built-in features over custom PHP implementations.
- Run or suggest validation commands:
    - `php yii migrate`
    - `php yii migrate/down`
    - `composer static`
    - `composer cs`
    - `composer tests`
- If detailed testing guides are required, refer to [internals.md](file:///d:/laragon/www/yii2-app-basic/docs/internals.md).

## Focus Areas

### Yii2 Performance

- Prevent N+1 query problems using `with()`.
- Use `joinWith()` only when filtering or sorting by relation fields.
- Use `asArray()` for read-only list APIs when ActiveRecord objects are not needed.
- Use `batch()` and `each()` for large dataset processing.
- Use `select()` to avoid loading unnecessary columns.
- Use `ActiveDataProvider` and `SearchModel` for pagination, filtering, and sorting.

### Database Optimization

- Analyze query patterns before adding indexes.
- Recommend indexes for columns used in:
    - `WHERE`
    - `JOIN`
    - `ORDER BY`
    - `GROUP BY`
    - foreign keys
    - unique constraints
- Suggest composite indexes based on actual query order.
- Avoid over-indexing because it slows down insert, update, and delete operations.
- Use `EXPLAIN` to verify whether indexes are being used.
- Detect slow query risks such as:
    - full table scan
    - missing join index
    - large offset pagination
    - unnecessary eager loading
    - duplicate rows from `joinWith()` on `hasMany`

### Migration Index Review

When reviewing migration files:

- Check whether foreign key columns have indexes.
- Check whether unique columns such as `slug`, `sku`, `code`, and `email` have unique indexes.
- Check whether polymorphic tables such as `resources` have composite indexes.
- Check whether common filters have suitable indexes.
- Check whether `safeDown()` properly drops indexes in reverse order.
- Do not create duplicate indexes.
- If an index is missing, create a new migration for it instead of editing old migrations that have already been applied.

### Recommended Index Patterns

For `products`:

- `slug` should be unique.
- `status, created_at` for active product listing.
- `category_id, status` for category filter.
- `brand_id, status` for brand filter.

For `product_variants`:

- `product_id`
- `sku` unique
- `product_id, is_active`
- `price` if filtering or sorting by price.

For `resources`:

- `resource_type, resource_id`
- `resource_type, resource_id, type, is_primary`
- `file_id`

For `files`:

- `mime_type, created_at`
- `user_id, created_at`

For `orders`:

- `user_id`
- `status, created_at`
- `code` unique

### Modern PHP 8.2+

- Use `declare(strict_types=1);`.
- Use constructor property promotion when suitable.
- Use `match` expressions when cleaner than `switch`.
- Use enums for fixed statuses or types.
- Use readonly properties/classes when data should not change.
- Prefer typed properties and return types.

### Security

- Use Yii2 Query Builder or parameter binding to prevent SQL injection.
- Validate all request input through FormModel or rules.
- Use `Html::encode()` when rendering user content in views.
- Validate upload file type, size, extension, and MIME type.
- Do not expose sensitive fields in API response.

## Approach

1. Read existing model relations, migrations, and query usage.
2. Identify the API query pattern first.
3. Check for N+1 query problems.
4. Check whether `with()`, `joinWith()`, `select()`, or `asArray()` is appropriate.
5. Review existing migration indexes.
6. Detect missing, duplicate, or poorly ordered indexes.
7. Suggest optimized indexes.
8. If needed, create a new migration for missing indexes.
9. Verify with `EXPLAIN` and Yii2 Debug Toolbar query count.
10. Run validation commands when possible.

## Output

- Optimized Yii2 ActiveRecord or Query Builder code.
- Migration files for missing indexes.
- Explanation of why each index is needed.
- Warning when an index may be unnecessary.
- Query optimization suggestions.
- N+1 detection and eager loading improvements.
- Clean PHP 8.2+ code passing static analysis and style checks.
- Verification steps and commands.

## Example Prompts

```text
Review all Yii2 migrations and check whether indexes are optimized.
```
