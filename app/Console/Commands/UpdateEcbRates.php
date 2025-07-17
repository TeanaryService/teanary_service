<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\LocaleCurrencyService;

class UpdateEcbRates extends Command
{
    protected $signature = 'app:update-ecb';
    protected $description = 'Fetch currency exchange rates from ECB and update the currencies table';

    public function handle()
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

        $response = Http::get($url);

        if (! $response->ok()) {
            $this->error('Failed to fetch ECB rates.');
            return;
        }

        $xml = simplexml_load_string($response->body());
        $cube = $xml->Cube->Cube;
        if (! $cube) {
            $this->error('Invalid ECB XML structure.');
            return;
        }

        $service = new LocaleCurrencyService();

        $currencies = $service->getCurrencies();
        $defaultCurrency = $currencies->firstWhere('default', true);

        if (! $defaultCurrency) {
            $this->error('No default currency found in the database.');
            return;
        }

        $defaultCode = $defaultCurrency->code;

        // Step 1: 构造 ECB 汇率表（EUR -> 其它币）
        $ecbRates = [];
        foreach ($cube->Cube as $rateNode) {
            $currencyCode = (string) $rateNode['currency'];
            $rate = (float) $rateNode['rate'];
            $ecbRates[$currencyCode] = $rate;
        }

        // Step 2: 检查 ECB 是否提供了默认币的汇率
        if ($defaultCode !== 'EUR' && ! isset($ecbRates[$defaultCode])) {
            $this->error("Default currency [{$defaultCode}] not found in ECB rates.");
            return;
        }

        // Step 3: 设置默认货币汇率为 1.0（作为锚点）
        $defaultCurrency->exchange_rate = 1.0;
        $defaultCurrency->save();

        // Step 4: 获取 EUR → 默认币 的汇率，用于推算其它币相对默认币的汇率
        $eurToDefault = $defaultCode === 'EUR' ? 1.0 : $ecbRates[$defaultCode];

        $updated = 0;

        foreach ($currencies as $currency) {
            $code = $currency->code;

            if ($code === $defaultCode) {
                continue; // 默认货币已设为 1.0
            }

            if ($code === 'EUR' && $defaultCode !== 'EUR') {
                // 如果 EUR 在数据库中，推算 EUR 的汇率
                $currency->exchange_rate = 1 / $eurToDefault;
                $currency->save();
                $updated++;
                continue;
            }

            // 其它币种，需存在于 ECB 汇率中
            if (! isset($ecbRates[$code])) {
                continue;
            }

            // rate(币种) = ECB(EUR→币种) / ECB(EUR→默认币)
            $convertedRate = $ecbRates[$code] / $eurToDefault;

            $currency->exchange_rate = $convertedRate;
            $currency->save();
            $updated++;
        }

        $service->clearCurrenciesCache();
        $this->info("Updated rates for {$updated} currencies relative to {$defaultCode}.");
    }
}