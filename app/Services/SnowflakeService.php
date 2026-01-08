<?php

namespace App\Services;

/**
 * 雪花ID生成器
 * 
 * 生成全局唯一的64位整数ID
 * 格式：1位符号位 + 41位时间戳 + 10位机器ID + 12位序列号
 */
class SnowflakeService
{
    // 时间戳起始点（2024-01-01 00:00:00）
    private const EPOCH = 1704067200000;

    // 机器ID位数
    private const MACHINE_ID_BITS = 10;

    // 序列号位数
    private const SEQUENCE_BITS = 12;

    // 机器ID最大值
    private const MAX_MACHINE_ID = (1 << self::MACHINE_ID_BITS) - 1;

    // 序列号最大值
    private const MAX_SEQUENCE = (1 << self::SEQUENCE_BITS) - 1;

    // 机器ID左移位数
    private const MACHINE_ID_SHIFT = self::SEQUENCE_BITS;

    // 时间戳左移位数
    private const TIMESTAMP_SHIFT = self::SEQUENCE_BITS + self::MACHINE_ID_BITS;

    private int $machineId;
    private int $sequence = 0;
    private int $lastTimestamp = -1;

    public function __construct(?int $machineId = null)
    {
        // 从环境变量获取机器ID，如果没有则使用IP地址的最后一段
        $this->machineId = $machineId ?? $this->getMachineId();
        
        if ($this->machineId < 0 || $this->machineId > self::MAX_MACHINE_ID) {
            throw new \InvalidArgumentException("机器ID必须在0到" . self::MAX_MACHINE_ID . "之间");
        }
    }

    /**
     * 生成雪花ID
     */
    public function nextId(): int
    {
        $timestamp = $this->currentTimestamp();

        // 如果时间回退，抛出异常
        if ($timestamp < $this->lastTimestamp) {
            throw new \RuntimeException("时钟回退，无法生成ID");
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

        // 生成ID：时间戳 + 机器ID + 序列号
        return (($timestamp - self::EPOCH) << self::TIMESTAMP_SHIFT)
            | ($this->machineId << self::MACHINE_ID_SHIFT)
            | $this->sequence;
    }

    /**
     * 获取当前时间戳（毫秒）
     */
    private function currentTimestamp(): int
    {
        return (int) (microtime(true) * 1000);
    }

    /**
     * 等待下一毫秒
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
     * 优先使用环境变量，否则使用IP地址的最后一段
     */
    private function getMachineId(): int
    {
        // 从环境变量获取
        $envMachineId = env('SNOWFLAKE_MACHINE_ID');
        if ($envMachineId !== null) {
            return (int) $envMachineId;
        }

        // 使用IP地址的最后一段
        $ip = gethostbyname(gethostname());
        if ($ip && $ip !== gethostname()) {
            $parts = explode('.', $ip);
            $lastPart = (int) end($parts);
            return $lastPart % (self::MAX_MACHINE_ID + 1);
        }

        // 默认使用随机数
        return mt_rand(0, self::MAX_MACHINE_ID);
    }

    /**
     * 从雪花ID解析时间戳
     */
    public static function parseTimestamp(int $id): int
    {
        return (($id >> self::TIMESTAMP_SHIFT) & ((1 << 41) - 1)) + self::EPOCH;
    }

    /**
     * 从雪花ID解析机器ID
     */
    public static function parseMachineId(int $id): int
    {
        return ($id >> self::MACHINE_ID_SHIFT) & self::MAX_MACHINE_ID;
    }

    /**
     * 从雪花ID解析序列号
     */
    public static function parseSequence(int $id): int
    {
        return $id & self::MAX_SEQUENCE;
    }
}
