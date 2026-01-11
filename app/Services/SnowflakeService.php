<?php

namespace App\Services;

/**
 * 雪花ID生成器.
 *
 * 生成全局唯一的45位整数ID（兼容JavaScript安全整数范围）
 * 格式：32位时间戳 + 7位机器ID + 6位序列号
 * 
 * 分配说明：
 * - 32位时间戳：约136年（从2024年开始，足够60年需求）
 * - 7位机器ID：支持0-127共128台机器（满足100台需求）
 * - 8位序列号：每毫秒可生成64个ID
 */
class SnowflakeService
{
    // 时间戳起始点（2024-01-01 00:00:00）
    private const EPOCH = 1704067200000;

    // 时间戳位数
    private const TIMESTAMP_BITS = 32;

    // 机器ID位数
    private const MACHINE_ID_BITS = 7;

    // 序列号位数
    private const SEQUENCE_BITS = 8;

    // 机器ID最大值
    private const MAX_MACHINE_ID = (1 << self::MACHINE_ID_BITS) - 1;

    // 序列号最大值
    private const MAX_SEQUENCE = (1 << self::SEQUENCE_BITS) - 1;

    // 机器ID左移位数
    private const MACHINE_ID_SHIFT = self::SEQUENCE_BITS;

    // 时间戳左移位数
    private const TIMESTAMP_SHIFT = self::SEQUENCE_BITS + self::MACHINE_ID_BITS;

    // 时间戳最大值（用于掩码）
    private const MAX_TIMESTAMP = (1 << self::TIMESTAMP_BITS) - 1;

    private int $machineId;
    private int $sequence = 0;
    private int $lastTimestamp = -1;

    public function __construct(?int $machineId = null)
    {
        // 从环境变量获取机器ID，如果没有则使用IP地址的最后一段
        $this->machineId = $machineId ?? $this->getMachineId();

        if ($this->machineId < 0 || $this->machineId > self::MAX_MACHINE_ID) {
            throw new \InvalidArgumentException('机器ID必须在0到'.self::MAX_MACHINE_ID.'之间');
        }
    }

    /**
     * 生成雪花ID.
     */
    public function nextId(): int
    {
        $timestamp = $this->currentTimestamp();

        // 如果时间回退，抛出异常
        if ($timestamp < $this->lastTimestamp) {
            throw new \RuntimeException('时钟回退，无法生成ID');
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
        $relativeTimestamp = $timestamp - self::EPOCH;
        
        // 检查时间戳是否超出范围
        if ($relativeTimestamp > self::MAX_TIMESTAMP) {
            throw new \RuntimeException('时间戳超出范围，无法生成ID');
        }

        // 生成ID：时间戳 + 机器ID + 序列号
        return ($relativeTimestamp << self::TIMESTAMP_SHIFT)
            | ($this->machineId << self::MACHINE_ID_SHIFT)
            | $this->sequence;
    }

    /**
     * 获取当前时间戳（毫秒）.
     */
    private function currentTimestamp(): int
    {
        return (int) (microtime(true) * 1000);
    }

    /**
     * 等待下一毫秒.
     */
    private function waitNextMillis(int $lastTimestamp): int
    {
        $timestamp = $this->currentTimestamp();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->currentTimestamp();
        }

        return $timestamp;
    }

    /**
     * 获取机器ID
     * 优先使用配置文件中的机器ID，否则使用IP地址的最后一段.
     */
    private function getMachineId(): int
    {
        // 从配置文件获取（配置文件会读取环境变量）
        $configMachineId = config('snowflake.machine_id');
        if ($configMachineId !== null) {
            return (int) $configMachineId;
        }

        // 使用IP地址的最后一段
        $ip = gethostbyname(gethostname());
        if ($ip && $ip !== gethostname()) {
            $parts = explode('.', $ip);
            $lastPart = (int) end($parts);

            return $lastPart % (self::MAX_MACHINE_ID + 1);
        }

        // 默认使用随机数（不推荐，可能导致ID冲突）
        return mt_rand(0, self::MAX_MACHINE_ID);
    }

    /**
     * 从雪花ID解析时间戳.
     */
    public static function parseTimestamp(int $id): int
    {
        return (($id >> self::TIMESTAMP_SHIFT) & self::MAX_TIMESTAMP) + self::EPOCH;
    }

    /**
     * 从雪花ID解析机器ID.
     */
    public static function parseMachineId(int $id): int
    {
        return ($id >> self::MACHINE_ID_SHIFT) & self::MAX_MACHINE_ID;
    }

    /**
     * 从雪花ID解析序列号.
     */
    public static function parseSequence(int $id): int
    {
        return $id & self::MAX_SEQUENCE;
    }
}
