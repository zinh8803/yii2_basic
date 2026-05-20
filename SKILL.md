---
name: php-pro
description: 'Write idiomatic PHP 8.2+ and Yii2 code focusing on performance, memory efficiency, and security.'
risk: unknown
source: community
date_added: '2026-02-27'
---

## Use this skill when

- Working on PHP or Yii2 framework tasks or workflows
- Needing guidance, performance optimization, or verification checklists for Yii2 application structure

## Do not use this skill when

- The task is unrelated to PHP/Yii2
- You need a different domain or frontend-only tools outside this scope

## Instructions

- Clarify goals, database constraints, and required inputs before coding.
- Apply Yii2 design patterns (ActiveRecord, Components, Behaviors) and validate outcomes.
- Run local validation commands (`composer cs-fix`, `composer static`, `composer tests`) to verify the code quality and correctness.
- If detailed testing guides are required, refer to [internals.md](file:///d:/laragon/www/yii2-app-basic/docs/internals.md).

## Focus Areas

- **Yii2 Performance**: Memory-efficient ActiveRecord queries (use `asArray()`, batch queries via `batch()` / `each()`, and eager loading using `with()` to prevent N+1 query issues).
- **Modern PHP 8.2+**: Use constructor property promotion, match expressions, enums, readonly classes/properties, and strict type safety.
- **Yii2 Component Mastery**: Leverage Yii2 Helpers (`ArrayHelper`, `Html`, `Json`), DI Container, and Behaviors instead of custom PHP implementations.
- **Security**: Prevent SQL injection by using Yii2 Query Builder parameter binding, and prevent XSS using `Html::encode()`.
- **Quality Tools**: Strict adherence to PSR compliance and static analysis rules configured in PHPStan and PHPCS.

## Approach

1. Start with built-in Yii2 components and helper functions before writing custom implementations.
2. Use `asArray()` and `batch()`/`each()` for memory-efficient database processing on large datasets.
3. Apply strict typing (`declare(strict_types=1);`) and leverage type inference.
4. Profile database queries and application bottlenecks using the Yii2 Debug toolbar/log targets.
5. Handle errors using Yii2's standard exception handling mechanism (e.g., `yii\web\HttpException`).
6. Write unit, functional, or acceptance tests using Codeception when introducing new logic.

## Output

- Memory-efficient ActiveRecord code avoiding N+1 queries.
- Clean code passing PHPStan static analysis (`composer static`) and PHPCS style check (`composer cs`).
- Proper security practices (input validation, SQL/XSS prevention).
- Actionable steps with commands run to verify that tests are passing (`composer tests`).

## Limitations
- Use this skill only when the task clearly matches the scope described above.
- Always run local validations (`composer static` and `composer cs`) to prevent pushing code that violates project style guidelines.
