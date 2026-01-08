<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddSnowflakeIdTrait extends Command
{
    protected $signature = 'app:add-snowflake-id-trait';
    protected $description = '为所有使用 Syncable 的模型添加 HasSnowflakeId trait';

    public function handle(): int
    {
        $modelsPath = app_path('Models');
        $files = File::allFiles($modelsPath);

        $updated = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $path = $file->getPathname();
            $content = File::get($path);

            // 跳过同步表模型
            if (str_contains($path, 'SyncLog') || str_contains($path, 'SyncStatus')) {
                continue;
            }

            // 跳过基础数据模型（多节点应保持一致，不使用雪花ID）
            $baseDataModels = [
                'Country.php',
                'CountryTranslation.php',
                'Zone.php',
                'ZoneTranslation.php',
                'Language.php',
                'Currency.php',
                'PromotionUserGroup.php',
            ];

            $shouldSkip = false;
            foreach ($baseDataModels as $modelName) {
                if (str_ends_with($path, $modelName)) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                ++$skipped;
                continue;
            }

            // 检查是否使用 Syncable
            if (! str_contains($content, 'use Syncable') && ! str_contains($content, 'use App\\Traits\\Syncable')) {
                continue;
            }

            // 检查是否已经有 HasSnowflakeId
            if (str_contains($content, 'HasSnowflakeId')) {
                ++$skipped;
                continue;
            }

            // 添加 use 语句
            if (str_contains($content, 'use App\\Traits\\Syncable;')) {
                $content = str_replace(
                    'use App\\Traits\\Syncable;',
                    "use App\\Traits\\HasSnowflakeId;\nuse App\\Traits\\Syncable;",
                    $content
                );
            } elseif (str_contains($content, 'use Syncable;')) {
                $content = str_replace(
                    'use Syncable;',
                    "use HasSnowflakeId;\n    use Syncable;",
                    $content
                );
            }

            // 在类中添加 trait
            if (preg_match('/class\s+\w+\s+extends.*?\{/s', $content, $matches)) {
                $classStart = $matches[0];
                // 查找第一个 use 语句的位置
                if (preg_match('/class\s+\w+\s+extends.*?\{([^}]*?)(use\s+\w+;)/s', $content, $useMatches)) {
                    // 在第一个 use 语句后添加 HasSnowflakeId
                    $firstUse = $useMatches[2];
                    if (! str_contains($firstUse, 'HasSnowflakeId')) {
                        $content = str_replace(
                            $firstUse,
                            $firstUse."\n    use HasSnowflakeId;",
                            $content
                        );
                    }
                } else {
                    // 如果没有找到 use 语句，在类开始后添加
                    $content = str_replace(
                        $classStart,
                        $classStart."\n    use HasSnowflakeId;",
                        $content
                    );
                }
            }

            File::put($path, $content);
            ++$updated;
            $this->info('Updated: '.basename($path));
        }

        $this->info("完成！更新了 {$updated} 个文件，跳过了 {$skipped} 个文件");

        return Command::SUCCESS;
    }
}
