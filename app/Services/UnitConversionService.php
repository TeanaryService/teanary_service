<?php

namespace App\Services;

class UnitConversionService
{
    /**
     * 换算比例表（基于中间单位）
     */
    protected array $lengthRates = [
        'mm' => 0.1,          // 1 mm = 0.1 cm
        'cm' => 1,
        'm'  => 100,          // 1 m = 100 cm
        'in' => 2.54,         // 1 in = 2.54 cm
        'ft' => 30.48,        // 1 ft = 30.48 cm
    ];

    protected array $weightRates = [
        'g'  => 1,               // 基础单位
        'kg' => 1000,            // 1 kg = 1000 g
        'lb' => 453.592,         // 1 lb = 453.592 g
        'oz' => 28.3495,         // 1 oz = 28.3495 g
    ];


    /**
     * 长度换算
     */
    public function convertLength(float $value, string $from, string $to): float
    {
        $from = strtolower($from);
        $to = strtolower($to);

        if (!isset($this->lengthRates[$from]) || !isset($this->lengthRates[$to])) {
            throw new \InvalidArgumentException("Unsupported length unit: $from or $to");
        }

        $baseValue = $value * $this->lengthRates[$from];  // 转为 cm
        return $baseValue / $this->lengthRates[$to];      // 转为目标单位
    }

    /**
     * 重量换算
     */
    public function convertWeight(float $value, string $from, string $to): float
    {
        $from = strtolower($from);
        $to = strtolower($to);

        if (!isset($this->weightRates[$from]) || !isset($this->weightRates[$to])) {
            throw new \InvalidArgumentException("Unsupported weight unit: $from or $to");
        }

        $baseValue = $value * $this->weightRates[$from];  // 转为 g
        return $baseValue / $this->weightRates[$to];      // 转为目标单位
    }
}
