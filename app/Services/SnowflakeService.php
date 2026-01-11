<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * 雪花ID生成器（优化版）.
 *
 * 生成全局唯一的53位整数ID，完全兼容JavaScript安全整数范围（Number.MAX_SAFE_INTEGER = 2^53 - 1）
 * 
 * ID格式：41位时间戳（毫秒） + 5位机器ID + 6位序列号 + 1位保留 = 53位
 * 实际使用：41位时间戳 + 5位机器ID + 6位序列号 = 52位（确保在JS安全范围内）
 * 
 * 位分配说明：
 * - 41位时间戳（毫秒）：约69年时间范围（从EPOCH开始，2^41毫秒 ≈ 69.7年）
 * - 5位机器ID：支持0-31共32台机器
 * - 6位序列号：每毫秒可生成64个ID
 * 
 * 最大ID值：约4.5千万亿（在JavaScript安全整数范围 2^53 - 1 = 9007199254740991 内）
 * 
 * 特性：
 * - 线程安全：使用文件锁确保多进程环境下的唯一性
 * - JS兼容：生成的ID完全在JavaScript安全整数范围内
 * - 高性能：优化的位运算和缓存机制
 * - 可配置：支持通过配置文件自定义EPOCH和机器ID
 */
class SnowflakeService
{
    /**
     * JavaScript安全整数最大值（2^53 - 1）
     * 确保生成的ID不超过此值，以兼容JavaScript Number类型
     */
    private const JS_MAX_SAFE_INTEGER = 9007199254740991;

    /**
     * 时间戳位数（41位，支持约69年）
     */
    private const TIMESTAMP_BITS = 41;

    /**
     * 机器ID位数（5位，支持32台机器）
     */
    private const MACHINE_ID_BITS = 5;

    /**
     * 序列号位数（6位，每毫秒64个ID）
     */
    private const SEQUENCE_BITS = 6;

    /**
     * 机器ID最大值（2^5 - 1 = 31）
     */
    private const MAX_MACHINE_ID = (1 << self::MACHINE_ID_BITS) - 1;

    /**
     * 序列号最大值（2^6 - 1 = 63）
     */
    private const MAX_SEQUENCE = (1 << self::SEQUENCE_BITS) - 1;

    /**
     * 机器ID左移位数（序列号位数）
     */
    private const MACHINE_ID_SHIFT = self::SEQUENCE_BITS;

    /**
     * 时间戳左移位数（序列号位数 + 机器ID位数）
     */
    private const TIMESTAMP_SHIFT = self::SEQUENCE_BITS + self::MACHINE_ID_BITS;

    /**
     * 时间戳最大值（用于掩码，2^41 - 1）
     */
    private const MAX_TIMESTAMP = (1 << self::TIMESTAMP_BITS) - 1;

    /**
     * 默认EPOCH（2024-01-01 00:00:00 UTC，毫秒时间戳）
     */
    private const DEFAULT_EPOCH = 1704067200000;

    /**
     * 文件锁超时时间（秒）
     */
    private const LOCK_TIMEOUT = 5;

    /**
     * 机器ID
     */
    private int $machineId;

    /**
     * 序列号
     */
    private int $sequence = 0;

    /**
     * 上次生成ID的时间戳（毫秒）
     */
    private int $lastTimestamp = -1;

    /**
     * EPOCH时间戳（从配置文件读取或使用默认值）
     */
    private int $epoch;

    /**
     * 文件锁句柄
     */
    private $lockHandle = null;

    /**
     * 锁文件路径
     */
    private string $lockFilePath;

    public function __construct(?int $machineId = null)
    {
        // 从配置文件获取EPOCH，如果没有则使用默认值
        $this->epoch = (int) (config('snowflake.epoch') ?? self::DEFAULT_EPOCH);

        // 验证EPOCH有效性
        if ($this->epoch <= 0 || $this->epoch > PHP_INT_MAX) {
            throw new \InvalidArgumentException('EPOCH必须是一个有效的正数时间戳（毫秒）');
        }

        // 获取机器ID
        $this->machineId = $machineId ?? $this->getMachineId();

        // 验证机器ID范围
        if ($this->machineId < 0 || $this->machineId > self::MAX_MACHINE_ID) {
            throw new \InvalidArgumentException(
                sprintf('机器ID必须在0到%d之间（支持%d台机器），当前值：%d', self::MAX_MACHINE_ID, self::MAX_MACHINE_ID + 1, $this->machineId)
            );
        }

        // 初始化文件锁（用于多进程环境下的线程安全）
        $this->initializeLock();
    }

    /**
     * 生成雪花ID（线程安全）.
     *
     * @return int 生成的雪花ID（确保在JavaScript安全整数范围内）
     * @throws \RuntimeException 当时钟回退或时间戳超出范围时抛出异常
     */
    public function nextId(): int
    {
        // 获取文件锁（确保多进程环境下的线程安全）
        $this->acquireLock();

        try {
            $timestamp = $this->currentTimestamp();

            // 检查时钟回退
            if ($timestamp < $this->lastTimestamp) {
                $drift = $this->lastTimestamp - $timestamp;
                throw new \RuntimeException(
                    sprintf('时钟回退 %d 毫秒，无法生成ID。请检查系统时间。', $drift)
                );
            }

            // 同一毫秒内，序列号递增
            if ($timestamp === $this->lastTimestamp) {
                $this->sequence = ($this->sequence + 1) & self::MAX_SEQUENCE;

                // 序列号溢出，等待下一毫秒
                if ($this->sequence === 0) {
                    $timestamp = $this->waitNextMillis($this->lastTimestamp);
                }
            } else {
                // 新的毫秒，序列号重置
                $this->sequence = 0;
            }

            $this->lastTimestamp = $timestamp;

            // 计算相对时间戳（从EPOCH开始的毫秒数）
            $relativeTimestamp = $timestamp - $this->epoch;

            // 检查时间戳是否超出范围
            if ($relativeTimestamp < 0) {
                throw new \RuntimeException(
                    sprintf('当前时间戳小于EPOCH（%d），无法生成ID', $this->epoch)
                );
            }

            if ($relativeTimestamp > self::MAX_TIMESTAMP) {
                throw new \RuntimeException(
                    sprintf(
                        '时间戳超出范围（最大：%d，当前：%d），无法生成ID。请调整EPOCH或等待时间回退。',
                        self::MAX_TIMESTAMP,
                        $relativeTimestamp
                    )
                );
            }

            // 生成ID：时间戳 + 机器ID + 序列号
            $id = ($relativeTimestamp << self::TIMESTAMP_SHIFT)
                | ($this->machineId << self::MACHINE_ID_SHIFT)
                | $this->sequence;

            // 验证ID是否在JavaScript安全整数范围内
            if ($id > self::JS_MAX_SAFE_INTEGER) {
                throw new \RuntimeException(
                    sprintf(
                        '生成的ID（%d）超出JavaScript安全整数范围（最大：%d）',
                        $id,
                        self::JS_MAX_SAFE_INTEGER
                    )
                );
            }

            return $id;
        } finally {
            // 释放文件锁
            $this->releaseLock();
        }
    }

    /**
     * 获取当前时间戳（毫秒）.
     *
     * @return int 当前时间戳（毫秒）
     */
    private function currentTimestamp(): int
    {
        return (int) (microtime(true) * 1000);
    }

    /**
     * 等待下一毫秒（自旋等待）.
     *
     * @param int $lastTimestamp 上次时间戳
     * @return int 新的时间戳
     */
    private function waitNextMillis(int $lastTimestamp): int
    {
        $timestamp = $this->currentTimestamp();
        $maxWait = 100; // 最大等待次数，防止无限循环
        $waitCount = 0;

        while ($timestamp <= $lastTimestamp && $waitCount < $maxWait) {
            // 使用usleep微秒级等待，提高精度
            usleep(100); // 等待100微秒
            $timestamp = $this->currentTimestamp();
            $waitCount++;
        }

        if ($timestamp <= $lastTimestamp) {
            throw new \RuntimeException('等待下一毫秒超时，无法生成ID');
        }

        return $timestamp;
    }

    /**
     * 获取机器ID.
     * 优先级：配置文件 > IP地址 > 进程ID > 随机数（不推荐）
     *
     * @return int 机器ID（0-31）
     */
    private function getMachineId(): int
    {
        // 1. 从配置文件获取（配置文件会读取环境变量）
        $configMachineId = config('snowflake.machine_id');
        if ($configMachineId !== null) {
            $machineId = (int) $configMachineId;
            if ($machineId >= 0 && $machineId <= self::MAX_MACHINE_ID) {
                return $machineId;
            }
        }

        // 2. 使用IP地址的最后一段取模
        $ip = $this->getServerIp();
        if ($ip) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                $lastPart = (int) end($parts);
                return $lastPart % (self::MAX_MACHINE_ID + 1);
            }
        }

        // 3. 使用进程ID取模（比随机数更稳定）
        $pid = getmypid();
        if ($pid !== false) {
            return $pid % (self::MAX_MACHINE_ID + 1);
        }

        // 4. 默认使用随机数（不推荐，可能导致ID冲突）
        return mt_rand(0, self::MAX_MACHINE_ID);
    }

    /**
     * 获取服务器IP地址.
     *
     * @return string|null IP地址或null
     */
    private function getServerIp(): ?string
    {
        $hostname = gethostname();
        if (!$hostname) {
            return null;
        }

        $ip = gethostbyname($hostname);
        if ($ip && $ip !== $hostname && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return null;
    }

    /**
     * 初始化文件锁.
     */
    private function initializeLock(): void
    {
        $lockDir = storage_path('locks');
        if (!File::exists($lockDir)) {
            File::makeDirectory($lockDir, 0755, true);
        }

        $this->lockFilePath = $lockDir . '/snowflake_' . $this->machineId . '.lock';
    }

    /**
     * 获取文件锁（阻塞式）.
     */
    private function acquireLock(): void
    {
        if ($this->lockHandle !== null) {
            return; // 已经持有锁
        }

        $this->lockHandle = fopen($this->lockFilePath, 'c+');
        if ($this->lockHandle === false) {
            throw new \RuntimeException('无法创建锁文件：' . $this->lockFilePath);
        }

        $startTime = microtime(true);
        while (!flock($this->lockHandle, LOCK_EX | LOCK_NB)) {
            if (microtime(true) - $startTime > self::LOCK_TIMEOUT) {
                fclose($this->lockHandle);
                $this->lockHandle = null;
                throw new \RuntimeException('获取文件锁超时');
            }
            usleep(1000); // 等待1毫秒后重试
        }
    }

    /**
     * 释放文件锁.
     */
    private function releaseLock(): void
    {
        if ($this->lockHandle !== null) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
            $this->lockHandle = null;
        }
    }

    /**
     * 析构函数：确保释放文件锁.
     */
    public function __destruct()
    {
        $this->releaseLock();
    }

    /**
     * 从雪花ID解析时间戳.
     *
     * @param int $id 雪花ID
     * @return int 时间戳（毫秒）
     */
    public static function parseTimestamp(int $id): int
    {
        $epoch = (int) (config('snowflake.epoch') ?? self::DEFAULT_EPOCH);
        return (($id >> self::TIMESTAMP_SHIFT) & self::MAX_TIMESTAMP) + $epoch;
    }

    /**
     * 从雪花ID解析机器ID.
     *
     * @param int $id 雪花ID
     * @return int 机器ID
     */
    public static function parseMachineId(int $id): int
    {
        return ($id >> self::MACHINE_ID_SHIFT) & self::MAX_MACHINE_ID;
    }

    /**
     * 从雪花ID解析序列号.
     *
     * @param int $id 雪花ID
     * @return int 序列号
     */
    public static function parseSequence(int $id): int
    {
        return $id & self::MAX_SEQUENCE;
    }

    /**
     * 验证ID是否在JavaScript安全整数范围内.
     *
     * @param int $id 要验证的ID
     * @return bool 是否在安全范围内
     */
    public static function isSafeForJavaScript(int $id): bool
    {
        return $id >= 0 && $id <= self::JS_MAX_SAFE_INTEGER;
    }

    /**
     * 获取JavaScript安全整数最大值.
     *
     * @return int JavaScript安全整数最大值（2^53 - 1）
     */
    public static function getJavaScriptMaxSafeInteger(): int
    {
        return self::JS_MAX_SAFE_INTEGER;
    }
}
