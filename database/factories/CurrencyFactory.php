<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 生成唯一的3位字母代码
        // 使用时间戳（微秒）和随机数确保唯一性
        $timestamp = (int) (microtime(true) * 1000000); // 微秒时间戳
        $random = random_int(1000, 9999);
        $unique = $timestamp.$random.uniqid('', true);
        $hash = md5($unique);

        // 从哈希中提取字母字符，确保是3位大写字母
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($hash));
        $code = substr($letters, 0, 3);

        // 如果字母不足3位，使用备用方案：从哈希中提取字符并转换为字母
        if (strlen($code) < 3) {
            // 将哈希的前6个字符转换为字母（A-Z）
            $hex = substr($hash, 0, 6);
            $code = '';
            for ($i = 0; $i < 3; ++$i) {
                $hexChar = substr($hex, $i * 2, 2);
                $num = hexdec($hexChar);
                $code .= chr(65 + ($num % 26)); // A-Z
            }
        }

        // 检查代码是否已存在，如果存在则重新生成
        $maxAttempts = 10;
        $attempts = 0;
        while ($attempts < $maxAttempts && Currency::where('code', $code)->exists()) {
            $unique = microtime(true).random_int(1000, 9999).uniqid('', true);
            $hash = md5($unique);
            $letters = preg_replace('/[^A-Z]/', '', strtoupper($hash));
            $code = substr($letters, 0, 3);
            if (strlen($code) < 3) {
                $hex = substr($hash, 0, 6);
                $code = '';
                for ($i = 0; $i < 3; ++$i) {
                    $hexChar = substr($hex, $i * 2, 2);
                    $num = hexdec($hexChar);
                    $code .= chr(65 + ($num % 26));
                }
            }
            ++$attempts;
        }

        // 常用货币列表（用于生成更真实的名称和符号）
        $currencies = [
            ['name' => 'US Dollar', 'symbol' => '$'],
            ['name' => 'Chinese Yuan', 'symbol' => '¥'],
            ['name' => 'Euro', 'symbol' => '€'],
            ['name' => 'British Pound', 'symbol' => '£'],
            ['name' => 'Japanese Yen', 'symbol' => '¥'],
        ];
        $currency = fake()->randomElement($currencies);

        return [
            'code' => $code,
            'name' => $currency['name'],
            'symbol' => $currency['symbol'],
            'exchange_rate' => fake()->randomFloat(4, 0.1, 10),
            'default' => false,
        ];
    }
}
