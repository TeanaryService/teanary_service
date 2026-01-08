<?php

namespace App\Console\Commands;

use App\Services\SnowflakeService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class MigrateToSnowflakeId extends Command
{
    protected $signature = 'app:migrate-to-snowflake-id 
                            {--dry-run : 只显示将要执行的操作，不实际迁移}
                            {--batch-size=100 : 每批处理的记录数}';

    protected $description = '将 test 库中使用自增ID的数据迁移到主库的雪花ID';

    private array $idMappings = []; // 存储旧ID到新ID的映射 [table => [oldId => newId]]
    private SnowflakeService $snowflakeService;
    private $oldConnection;
    private $newConnection;

    // 定义表的迁移顺序（按依赖关系）
    private array $migrationOrder = [
        // 基础数据表（ID保持原样，不转换为雪花ID，最先迁移）
        'languages',
        'currencies',
        'countries',
        'zones',
        'country_translations',
        'zone_translations',
        
        // 基础表（无外键依赖）
        'users',
        'managers',
        'categories',
        'attributes',
        'specifications',
        'products',
        'user_groups',
        'promotions',
        'contacts',
        'editor_uploads',
        
        // 依赖基础表的表
        'category_translations',
        'attribute_translations',
        'attribute_values',
        'attribute_value_translations',
        'specification_translations',
        'specification_values',
        'specification_value_translations',
        'product_translations',
        'product_variants',
        'product_reviews',
        'addresses',
        'carts',
        'cart_items',
        'orders',
        'order_items',
        'order_shipments',
        'articles',
        'article_translations',
        'promotion_translations',
        'promotion_rules',
        'user_group_translations',
        
        // media 表必须在所有可能被它引用的表之后迁移（因为它使用多态关联）
        'media',
        
        // 注意：中间表（product_category, product_attribute_value, product_variant_specification_value）
        // 不在 migrationOrder 中，它们会在后面单独处理
    ];

    public function __construct()
    {
        parent::__construct();
        $this->snowflakeService = app(SnowflakeService::class);
    }

    public function handle(): int
    {
        $this->info('开始迁移数据从 test 库到主库（自增ID -> 雪花ID）');

        // 配置旧数据库连接（test库）
        $defaultConfig = config('database.connections.'.config('database.default'));
        $oldConfig = array_merge($defaultConfig, ['database' => 'test']);
        
        config(['database.connections.test_old' => $oldConfig]);
        $this->oldConnection = DB::connection('test_old');
        $this->newConnection = DB::connection();

        // 测试连接
        try {
            $this->oldConnection->getPdo();
            $this->info('✓ 成功连接到 test 数据库');
        } catch (\Exception $e) {
            $this->error('✗ 无法连接到 test 数据库: '.$e->getMessage());
            return Command::FAILURE;
        }

        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');

        if ($dryRun) {
            $this->warn('⚠️  这是试运行模式，不会实际修改数据');
        }

        // 获取所有需要迁移的表
        $tables = $this->getTablesToMigrate();

        $this->info("\n将迁移以下表: ".implode(', ', $tables));
        
        if (!$this->confirm('是否继续？', true)) {
            return Command::FAILURE;
        }

        // 清空要迁移的表
        if (!$dryRun) {
            $this->info("\n开始清空目标表...");
            $this->clearTables($tables);
            $this->info("✓ 表清空完成");
        } else {
            $this->info("\n[试运行] 将清空以下表: ".implode(', ', $tables));
        }
        
        $totalRecords = 0;
        $totalErrors = 0;

        // 按顺序迁移每个表
        foreach ($this->migrationOrder as $table) {
            if (!in_array($table, $tables)) {
                continue;
            }

            $this->info("\n处理表: {$table}");
            
            try {
                $result = $this->migrateTable($table, $dryRun, $batchSize);
                $totalRecords += $result['count'];
                $totalErrors += $result['errors'];
                
                $this->info("  ✓ 迁移 {$result['count']} 条记录");
                if ($result['errors'] > 0) {
                    $this->warn("  ⚠  {$result['errors']} 条记录失败");
                }
            } catch (\Exception $e) {
                $this->error("  ✗ 迁移失败: ".$e->getMessage());
                $totalErrors++;
            }
        }

        // 迁移中间表
        $this->info("\n处理中间表（多对多关系）");
        foreach (['product_category', 'product_attribute_value', 'product_variant_specification_value', 'promotion_user_group', 'promotion_product_variant'] as $pivotTable) {
            if (!in_array($pivotTable, $tables)) {
                continue;
            }
            
            try {
                $result = $this->migratePivotTable($pivotTable, $dryRun);
                $totalRecords += $result['count'];
                $totalErrors += $result['errors'];
                
                $this->info("  ✓ 迁移 {$result['count']} 条记录");
                if ($result['errors'] > 0) {
                    $this->warn("  ⚠  {$result['errors']} 条记录失败");
                }
            } catch (\Exception $e) {
                $this->error("  ✗ 迁移失败: ".$e->getMessage());
                $totalErrors++;
            }
        }

        $this->info("\n迁移完成！");
        $this->info("总计: {$totalRecords} 条记录");
        if ($totalErrors > 0) {
            $this->warn("错误: {$totalErrors} 条记录");
        }

        return Command::SUCCESS;
    }

    /**
     * 获取需要迁移的表列表
     */
    private function getTablesToMigrate(): array
    {
        $tables = [];
        
        // 获取所有使用 HasSnowflakeId 的模型
        $modelsPath = app_path('Models');
        $files = glob($modelsPath.'/*.php');
        
        foreach ($files as $file) {
            $className = 'App\\Models\\'.basename($file, '.php');
            
            if (!class_exists($className)) {
                continue;
            }
            
            $reflection = new ReflectionClass($className);
            
            // 检查是否使用 HasSnowflakeId trait
            if (!$reflection->hasMethod('bootHasSnowflakeId') && 
                !in_array('App\\Traits\\HasSnowflakeId', $reflection->getTraitNames())) {
                continue;
            }
            
            // 跳过同步表模型
            $skipModels = [
                'PromotionUserGroup', 'SyncLog', 'SyncStatus'
            ];
            
            if (in_array(basename($file, '.php'), $skipModels)) {
                continue;
            }
            
            try {
                $model = new $className();
                $table = $model->getTable();
                $tables[] = $table;
            } catch (\Exception $e) {
                // 忽略无法实例化的模型
            }
        }
        
        // 添加中间表
        $tables[] = 'product_category';
        $tables[] = 'product_attribute_value';
        $tables[] = 'product_variant_specification_value';
        $tables[] = 'promotion_user_group';
        $tables[] = 'promotion_product_variant';
        
        // 添加基础数据表（ID保持原样，不转换为雪花ID）
        $baseDataTables = ['languages', 'currencies', 'countries', 'country_translations', 'zones', 'zone_translations'];
        foreach ($baseDataTables as $baseTable) {
            if (Schema::connection('test_old')->hasTable($baseTable)) {
                $tables[] = $baseTable;
            }
        }
        
        return array_unique($tables);
    }

    /**
     * 判断表是否是基础数据表（ID保持原样，不转换为雪花ID）
     */
    private function isBaseDataTable(string $table): bool
    {
        $baseDataTables = [
            'languages',
            'currencies',
            'countries',
            'country_translations',
            'zones',
            'zone_translations',
        ];
        
        return in_array($table, $baseDataTables);
    }

    /**
     * 迁移单个表
     */
    private function migrateTable(string $table, bool $dryRun, int $batchSize): array
    {
        $count = 0;
        $errors = 0;
        
        // 检查旧表是否存在
        if (!Schema::connection('test_old')->hasTable($table)) {
            $this->warn("  表 {$table} 在 test 库中不存在，跳过");
            return ['count' => 0, 'errors' => 0];
        }
        
        // 检查新表是否存在
        if (!Schema::hasTable($table)) {
            $this->warn("  表 {$table} 在主库中不存在，跳过");
            return ['count' => 0, 'errors' => 0];
        }
        
        // 获取旧表的所有数据
        $oldRecords = $this->oldConnection->table($table)->get();
        
        if ($oldRecords->isEmpty()) {
            return ['count' => 0, 'errors' => 0];
        }
        
        // 获取表结构
        $columns = Schema::getColumnListing($table);
        $idColumn = 'id';
        
        // 初始化ID映射
        if (!isset($this->idMappings[$table])) {
            $this->idMappings[$table] = [];
        }
        
        // 判断是否是基础数据表（ID保持原样）
        $isBaseDataTable = $this->isBaseDataTable($table);
        
        // 批量处理
        foreach ($oldRecords->chunk($batchSize) as $chunk) {
            foreach ($chunk as $oldRecord) {
                try {
                    $oldId = $oldRecord->id;
                    
                    // 准备新记录数据
                    $newData = (array) $oldRecord;
                    
                    if ($isBaseDataTable) {
                        // 基础数据表：ID保持原样，不转换
                        $newId = $oldId;
                    } else {
                        // 其他表：生成新的雪花ID
                        $newId = $this->snowflakeService->nextId();
                        // 保存映射
                        if (!isset($this->idMappings[$table])) {
                            $this->idMappings[$table] = [];
                        }
                        $this->idMappings[$table][$oldId] = $newId;
                    }
                    
                    $newData['id'] = $newId;
                    
                    // 更新外键字段
                    $newData = $this->updateForeignKeys($table, $newData);
                    
                    // 移除不需要的字段
                    $newData = array_intersect_key($newData, array_flip($columns));
                    
                    if (!$dryRun) {
                        // 检查记录是否已存在（避免重复插入）
                        $exists = $this->newConnection->table($table)->where('id', $newId)->exists();
                        if (!$exists) {
                            $this->newConnection->table($table)->insert($newData);
                        } else {
                            $this->warn("    记录 ID {$newId} 已存在，跳过");
                        }
                    }
                    
                    $count++;
                    
                    if ($count % 100 == 0) {
                        $this->line("    已处理 {$count} 条记录...");
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->warn("    记录 ID {$oldRecord->id} 迁移失败: ".$e->getMessage());
                }
            }
        }
        
        return ['count' => $count, 'errors' => $errors];
    }

    /**
     * 迁移中间表（多对多关系）
     */
    private function migratePivotTable(string $table, bool $dryRun): array
    {
        $count = 0;
        $errors = 0;
        
        // 检查表是否存在
        if (!Schema::connection('test_old')->hasTable($table) || !Schema::hasTable($table)) {
            return ['count' => 0, 'errors' => 0];
        }
        
        // 获取旧表的所有数据
        $oldRecords = $this->oldConnection->table($table)->get();
        
        if ($oldRecords->isEmpty()) {
            return ['count' => 0, 'errors' => 0];
        }
        
        // 确定外键字段
        $foreignKeys = $this->getPivotTableForeignKeys($table);
        
        foreach ($oldRecords as $oldRecord) {
            try {
                $newData = (array) $oldRecord;
                
                // 更新所有外键字段
                foreach ($foreignKeys as $fkColumn) {
                    if (isset($oldRecord->$fkColumn)) {
                        $oldFkId = $oldRecord->$fkColumn;
                        $relatedTable = $this->getRelatedTableForColumn($table, $fkColumn);
                        
                        // 如果引用的是基础数据表，ID保持原样，不需要映射
                        if ($relatedTable && $this->isBaseDataTable($relatedTable)) {
                            // 基础数据表的ID保持原样，不需要转换
                            continue;
                        }
                        
                        // 其他表需要映射转换
                        if ($relatedTable && isset($this->idMappings[$relatedTable][$oldFkId])) {
                            $newData[$fkColumn] = $this->idMappings[$relatedTable][$oldFkId];
                        } else {
                            // 如果找不到映射，跳过这条记录
                            throw new \Exception("找不到外键映射: {$fkColumn} = {$oldFkId}");
                        }
                    }
                }
                
                if (!$dryRun) {
                    // 检查记录是否已存在（使用唯一键判断）
                    $exists = false;
                    if (count($foreignKeys) >= 2) {
                        $query = $this->newConnection->table($table);
                        foreach ($foreignKeys as $fkColumn) {
                            $query->where($fkColumn, $newData[$fkColumn]);
                        }
                        $exists = $query->exists();
                    }
                    
                    if (!$exists) {
                        $this->newConnection->table($table)->insert($newData);
                    } else {
                        $this->warn("    记录已存在，跳过");
                    }
                }
                
                $count++;
            } catch (\Exception $e) {
                $errors++;
                $this->warn("    记录迁移失败: ".$e->getMessage());
            }
        }
        
        return ['count' => $count, 'errors' => $errors];
    }

    /**
     * 更新外键字段
     */
    private function updateForeignKeys(string $table, array $data): array
    {
        // 特殊处理 media 表的多态关联
        if ($table === 'media') {
            return $this->updateMediaForeignKeys($data);
        }
        
        // 定义表的外键关系
        $foreignKeys = [
            'categories' => ['parent_id' => 'categories'], // 自引用
            'category_translations' => ['category_id' => 'categories', 'language_id' => 'languages'],
            'attribute_translations' => ['attribute_id' => 'attributes', 'language_id' => 'languages'],
            'attribute_values' => ['attribute_id' => 'attributes'],
            'attribute_value_translations' => ['attribute_value_id' => 'attribute_values', 'language_id' => 'languages'],
            'specification_translations' => ['specification_id' => 'specifications', 'language_id' => 'languages'],
            'specification_values' => ['specification_id' => 'specifications'],
            'specification_value_translations' => ['specification_value_id' => 'specification_values', 'language_id' => 'languages'],
            'product_translations' => ['product_id' => 'products', 'language_id' => 'languages'],
            'product_variants' => ['product_id' => 'products'],
            'product_reviews' => ['product_id' => 'products'],
            'addresses' => ['user_id' => 'users', 'country_id' => 'countries', 'zone_id' => 'zones'],
            'carts' => ['user_id' => 'users'],
            'cart_items' => ['cart_id' => 'carts', 'product_id' => 'products', 'product_variant_id' => 'product_variants'],
            'orders' => ['user_id' => 'users', 'currency_id' => 'currencies', 'shipping_address_id' => 'addresses', 'billing_address_id' => 'addresses'],
            'order_items' => ['order_id' => 'orders', 'product_id' => 'products', 'product_variant_id' => 'product_variants'],
            'order_shipments' => ['order_id' => 'orders'],
            'articles' => ['user_id' => 'users'],
            'article_translations' => ['article_id' => 'articles', 'language_id' => 'languages'],
            'promotion_translations' => ['promotion_id' => 'promotions', 'language_id' => 'languages'],
            'promotion_rules' => ['promotion_id' => 'promotions'],
            'user_group_translations' => ['user_group_id' => 'user_groups', 'language_id' => 'languages'],
            'country_translations' => ['country_id' => 'countries', 'language_id' => 'languages'],
            'zone_translations' => ['zone_id' => 'zones', 'language_id' => 'languages'],
        ];
        
        if (!isset($foreignKeys[$table])) {
            return $data;
        }
        
        foreach ($foreignKeys[$table] as $fkColumn => $relatedTable) {
            if (isset($data[$fkColumn]) && $data[$fkColumn] !== null) {
                $oldFkId = $data[$fkColumn];
                
                // 如果引用的是基础数据表，ID保持原样，不需要映射
                if ($this->isBaseDataTable($relatedTable)) {
                    // 基础数据表的ID保持原样，不需要转换
                    continue;
                }
                
                // 其他表需要映射转换
                if (isset($this->idMappings[$relatedTable][$oldFkId])) {
                    $data[$fkColumn] = $this->idMappings[$relatedTable][$oldFkId];
                } else {
                    // 如果找不到映射，设置为 null（或者抛出异常）
                    $this->warn("    警告: 表 {$table} 的外键 {$fkColumn} = {$oldFkId} 找不到映射，设置为 null");
                    $data[$fkColumn] = null;
                }
            }
        }
        
        return $data;
    }

    /**
     * 更新 media 表的多态关联外键
     */
    private function updateMediaForeignKeys(array $data): array
    {
        // 模型类型到表名的映射（支持多种格式）
        $modelTypeToTable = [
            // 完整命名空间格式
            'App\\Models\\User' => 'users',
            'App\\Models\\Category' => 'categories',
            'App\\Models\\Product' => 'products',
            'App\\Models\\ProductVariant' => 'product_variants',
            'App\\Models\\ProductReview' => 'product_reviews',
            'App\\Models\\Article' => 'articles',
            'App\\Models\\PromotionRule' => 'promotion_rules',
            'App\\Models\\Manager' => 'managers',
            // 短名称格式（以防万一）
            'User' => 'users',
            'Category' => 'categories',
            'Product' => 'products',
            'ProductVariant' => 'product_variants',
            'ProductReview' => 'product_reviews',
            'Article' => 'articles',
            'PromotionRule' => 'promotion_rules',
            'Manager' => 'managers',
        ];
        
        if (isset($data['model_id']) && isset($data['model_type']) && $data['model_id'] !== null) {
            $modelType = $data['model_type'];
            $oldModelId = $data['model_id'];
            
            // 查找对应的表名
            $relatedTable = $modelTypeToTable[$modelType] ?? null;
            
            if ($relatedTable) {
                // 如果引用的是基础数据表，ID保持原样，不需要映射
                if ($this->isBaseDataTable($relatedTable)) {
                    // 基础数据表的ID保持原样，不需要转换
                    return $data;
                }
                
                // 其他表需要映射转换
                if (isset($this->idMappings[$relatedTable][$oldModelId])) {
                    $newModelId = $this->idMappings[$relatedTable][$oldModelId];
                    $data['model_id'] = $newModelId;
                    $this->line("    转换 media model_id: {$oldModelId} -> {$newModelId} (model_type: {$modelType}, table: {$relatedTable})");
                } else {
                    // 如果找不到映射，输出调试信息
                    $this->warn("    警告: media 表的 model_id = {$oldModelId} (model_type: {$modelType}, table: {$relatedTable}) 找不到映射");
                    $this->warn("    可用的映射表: " . implode(', ', array_keys($this->idMappings)));
                    if (isset($this->idMappings[$relatedTable])) {
                        $this->warn("    {$relatedTable} 表的映射数量: " . count($this->idMappings[$relatedTable]));
                    } else {
                        $this->warn("    {$relatedTable} 表的映射不存在");
                    }
                    $data['model_id'] = null;
                }
            } else {
                $this->warn("    警告: media 表的 model_type = {$modelType} 未在映射表中，跳过 model_id 转换");
            }
        }
        
        return $data;
    }

    /**
     * 获取中间表的外键字段
     */
    private function getPivotTableForeignKeys(string $table): array
    {
        $pivotKeys = [
            'product_category' => ['product_id', 'category_id'],
            'product_attribute_value' => ['attribute_id', 'product_id', 'attribute_value_id'],
            'product_variant_specification_value' => ['product_variant_id', 'specification_id', 'specification_value_id'],
            'promotion_user_group' => ['promotion_id', 'user_group_id'],
            'promotion_product_variant' => ['promotion_id', 'product_id', 'product_variant_id'],
        ];
        
        return $pivotKeys[$table] ?? [];
    }

    /**
     * 获取外键列对应的表名
     */
    private function getRelatedTableForColumn(string $table, string $column): ?string
    {
        $mappings = [
            'product_category' => [
                'product_id' => 'products',
                'category_id' => 'categories',
            ],
            'product_attribute_value' => [
                'attribute_id' => 'attributes',
                'product_id' => 'products',
                'attribute_value_id' => 'attribute_values',
            ],
            'product_variant_specification_value' => [
                'product_variant_id' => 'product_variants',
                'specification_id' => 'specifications',
                'specification_value_id' => 'specification_values',
            ],
            'promotion_user_group' => [
                'promotion_id' => 'promotions',
                'user_group_id' => 'user_groups',
            ],
            'promotion_product_variant' => [
                'promotion_id' => 'promotions',
                'product_id' => 'products',
                'product_variant_id' => 'product_variants',
            ],
        ];
        
        return $mappings[$table][$column] ?? null;
    }

    /**
     * 清空要迁移的表
     */
    private function clearTables(array $tables): void
    {
        // 按逆序清空（先清空依赖表，再清空基础表）
        $reverseOrder = array_reverse($this->migrationOrder);
        
        // 添加中间表到清空列表（中间表应该最先清空）
        $pivotTables = ['product_category', 'product_attribute_value', 'product_variant_specification_value', 'promotion_user_group', 'promotion_product_variant'];
        $tablesToClear = array_merge($pivotTables, $reverseOrder);
        
        // 去重并过滤出实际需要清空的表
        $tablesToClear = array_unique(array_filter($tablesToClear, function ($table) use ($tables) {
            return in_array($table, $tables) && Schema::hasTable($table);
        }));
        
        if (empty($tablesToClear)) {
            $this->warn("  没有需要清空的表");
            return;
        }
        
        // 禁用外键检查（MySQL/MariaDB）
        $driver = $this->newConnection->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            $this->newConnection->statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'pgsql') {
            // PostgreSQL 需要按表禁用外键
            // 这里先不处理，因为需要知道具体的外键关系
        }
        
        try {
            foreach ($tablesToClear as $table) {
                $this->line("  清空表: {$table}");
                $this->newConnection->table($table)->truncate();
            }
        } finally {
            // 重新启用外键检查
            if (in_array($driver, ['mysql', 'mariadb'])) {
                $this->newConnection->statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }
}
