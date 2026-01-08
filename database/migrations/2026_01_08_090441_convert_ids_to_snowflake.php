<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 需要转换为雪花ID的业务表列表
     * 排除：
     * - sync_logs, sync_statuses (同步日志表)
     * - jobs, job_batches, cache, cache_locks, failed_jobs, sessions, password_reset_tokens (系统表)
     * - languages, currencies, countries, country_translations, zones, zone_translations (基础数据，多节点应保持一致)
     */
    private array $tablesToConvert = [
        // 用户相关
        'users',
        'user_groups',
        'user_group_translations',
        'managers',
        
        // 地址（用户相关数据，可以使用雪花ID）
        'addresses',
        
        // 分类
        'categories',
        'category_translations',
        
        // 产品
        'products',
        'product_translations',
        'product_variants',
        'product_reviews',
        
        // 属性
        'attributes',
        'attribute_translations',
        'attribute_values',
        'attribute_value_translations',
        
        // 规格
        'specifications',
        'specification_translations',
        'specification_values',
        'specification_value_translations',
        
        // 购物车
        'carts',
        'cart_items',
        
        // 订单
        'orders',
        'order_items',
        'order_shipments',
        
        // 促销
        'promotions',
        'promotion_translations',
        'promotion_rules',
        
        // 文章
        'articles',
        'article_translations',
        
        // 其他
        'contacts',
        'editor_uploads',
        
        // 媒体文件
        'media',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tablesToConvert as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $this->convertTableToSnowflake($tableName);
        }
    }

    /**
     * 将表的主键ID从自增改为雪花ID
     */
    private function convertTableToSnowflake(string $tableName): void
    {
        $driver = DB::getDriverName();
        
        // MariaDB 与 MySQL 语法兼容，使用相同的转换逻辑
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $this->convertMysqlTable($tableName);
        } elseif ($driver === 'pgsql') {
            $this->convertPostgresTable($tableName);
        } else {
            throw new \RuntimeException("不支持的数据库驱动: {$driver}");
        }
    }

    /**
     * MySQL/MariaDB 转换
     */
    private function convertMysqlTable(string $tableName): void
    {
        // 检查表是否存在id字段
        $hasIdColumn = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = 'id'
        ", [$tableName]);

        if (!$hasIdColumn || $hasIdColumn->count == 0) {
            return;
        }

        // 获取当前id字段信息
        $idInfo = DB::selectOne("
            SELECT 
                COLUMN_TYPE,
                IS_NULLABLE,
                COLUMN_DEFAULT
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = 'id'
        ", [$tableName]);

        // 如果已经是bigint且非自增，跳过
        if (strpos($idInfo->COLUMN_TYPE, 'bigint') !== false && strpos($idInfo->COLUMN_TYPE, 'auto_increment') === false) {
            return;
        }

        // 删除自增属性（MySQL需要先删除自增，再修改类型）
        DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL");
        
        // 确保主键约束存在
        $hasPrimaryKey = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_TYPE = 'PRIMARY KEY'
        ", [$tableName]);

        if (!$hasPrimaryKey || $hasPrimaryKey->count == 0) {
            DB::statement("ALTER TABLE `{$tableName}` ADD PRIMARY KEY (`id`)");
        }
    }

    /**
     * PostgreSQL 转换
     */
    private function convertPostgresTable(string $tableName): void
    {
        // 检查表是否存在id字段
        $hasIdColumn = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = 'public' 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = 'id'
        ", [$tableName]);

        if (!$hasIdColumn || $hasIdColumn->count == 0) {
            return;
        }

        // 获取序列名称
        $sequenceInfo = DB::selectOne("
            SELECT 
                column_default
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = 'public' 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = 'id'
        ", [$tableName]);

        // 如果已经有默认值（序列），需要删除
        if ($sequenceInfo && $sequenceInfo->column_default) {
            // 提取序列名
            if (preg_match("/nextval\('([^']+)'/", $sequenceInfo->column_default, $matches)) {
                $sequenceName = $matches[1];
                // 删除默认值
                DB::statement("ALTER TABLE \"{$tableName}\" ALTER COLUMN \"id\" DROP DEFAULT");
                // 可选：删除序列（如果不再需要）
                // DB::statement("DROP SEQUENCE IF EXISTS {$sequenceName}");
            }
        }

        // 确保id字段是bigint类型
        DB::statement("ALTER TABLE \"{$tableName}\" ALTER COLUMN \"id\" TYPE BIGINT");
        DB::statement("ALTER TABLE \"{$tableName}\" ALTER COLUMN \"id\" SET NOT NULL");
    }

    /**
     * Reverse the migrations.
     * 
     * 注意：回滚操作需要谨慎，因为可能已经有雪花ID数据
     */
    public function down(): void
    {
        // 回滚操作比较复杂，因为需要恢复自增ID
        // 这里只提供基本框架，实际使用时需要根据具体情况调整
        
        foreach ($this->tablesToConvert as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $driver = DB::getDriverName();
            
            // MariaDB 与 MySQL 语法兼容
            if ($driver === 'mysql' || $driver === 'mariadb') {
                // MySQL/MariaDB 恢复自增（需要先清空表或重置自增值）
                // DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            } elseif ($driver === 'pgsql') {
                // PostgreSQL 恢复序列
                // 需要重新创建序列并设置默认值
            }
        }
    }
};
