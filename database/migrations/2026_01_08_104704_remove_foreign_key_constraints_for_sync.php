<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 需要移除外键约束的表列表
     * 排除：sync_logs, sync_statuses, jobs, job_batches, cache, cache_locks, failed_jobs, sessions, password_reset_tokens
     */
    private array $tables = [
        'categories',
        'category_translations',
        'products',
        'product_translations',
        'product_category',
        'attributes',
        'attribute_translations',
        'attribute_values',
        'attribute_value_translations',
        'specifications',
        'specification_translations',
        'specification_values',
        'specification_value_translations',
        'product_variants',
        'product_reviews',
        'product_variant_specification_value',
        'product_attribute_value',
        'carts',
        'cart_items',
        'orders',
        'order_items',
        'order_shipments',
        'promotions',
        'promotion_translations',
        'promotion_rules',
        'promotion_user_group',
        'promotion_product_variant',
        'articles',
        'article_translations',
        'contacts',
        'editor_uploads',
        'users',
        'user_groups',
        'user_group_translations',
        'managers',
        'addresses',
        'media',
    ];

    /**
     * Run the migrations.
     * 
     * 移除所有外键约束，改为在代码层面管理关联删除
     * 这样可以避免多节点同步时的外键约束问题
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $this->removeForeignKeysMysql();
        } elseif ($driver === 'pgsql') {
            $this->removeForeignKeysPostgres();
        } else {
            throw new \RuntimeException("不支持的数据库驱动: {$driver}");
        }
    }

    /**
     * MySQL/MariaDB 移除外键约束
     */
    private function removeForeignKeysMysql(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            // 获取该表的所有外键约束
            $foreignKeys = DB::select("
                SELECT 
                    CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName]);

            foreach ($foreignKeys as $fk) {
                $constraintName = $fk->CONSTRAINT_NAME;
                try {
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`");
                } catch (\Exception $e) {
                    // 忽略已不存在的约束
                    if (!str_contains($e->getMessage(), "doesn't exist")) {
                        \Log::warning("移除外键约束失败: {$tableName}.{$constraintName} - " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * PostgreSQL 移除外键约束
     */
    private function removeForeignKeysPostgres(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            // 获取该表的所有外键约束
            $foreignKeys = DB::select("
                SELECT
                    tc.constraint_name
                FROM information_schema.table_constraints AS tc
                JOIN information_schema.key_column_usage AS kcu
                    ON tc.constraint_name = kcu.constraint_name
                    AND tc.table_schema = kcu.table_schema
                WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_schema = 'public'
                AND tc.table_name = ?
            ", [$tableName]);

            foreach ($foreignKeys as $fk) {
                $constraintName = $fk->constraint_name;
                try {
                    DB::statement("ALTER TABLE \"{$tableName}\" DROP CONSTRAINT \"{$constraintName}\"");
                } catch (\Exception $e) {
                    // 忽略已不存在的约束
                    if (!str_contains($e->getMessage(), "does not exist")) {
                        \Log::warning("移除外键约束失败: {$tableName}.{$constraintName} - " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     * 
     * 注意：回滚操作需要重新创建外键约束，这比较复杂
     * 建议在生产环境中不要回滚此迁移
     */
    public function down(): void
    {
        // 回滚操作需要重新创建所有外键约束
        // 由于外键约束很多且复杂，这里不提供自动回滚
        // 如果需要回滚，请手动重新创建外键约束
        
        \Log::warning('此迁移不支持自动回滚，如需回滚请手动重新创建外键约束');
    }
};
