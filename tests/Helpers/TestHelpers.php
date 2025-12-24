<?php

namespace Tests\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * 测试辅助函数
 */
class TestHelpers
{
    /**
     * 创建并登录用户
     */
    public static function actingAsUser(?User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        Auth::login($user);
        return $user;
    }

    /**
     * 创建管理员用户
     */
    public static function actingAsAdmin(): User
    {
        // 根据实际的管理员模型调整
        $user = User::factory()->create();
        // 如果需要设置管理员权限，在这里添加
        Auth::login($user);
        return $user;
    }

    /**
     * 断言数据库中存在记录
     */
    public static function assertDatabaseHas(string $table, array $data): void
    {
        \Illuminate\Support\Facades\DB::table($table)->where($data)->firstOrFail();
    }

    /**
     * 断言数据库中不存在记录
     */
    public static function assertDatabaseMissing(string $table, array $data): void
    {
        $exists = \Illuminate\Support\Facades\DB::table($table)->where($data)->exists();
        if ($exists) {
            throw new \PHPUnit\Framework\AssertionFailedError("Found unexpected record in {$table}");
        }
    }
}

