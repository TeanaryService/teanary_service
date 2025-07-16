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

        // 找到默认币种
        $defaultCurrency = $service->getCurrencies()->firstWhere('default', true);

        if (! $defaultCurrency) {
            $this->error('No default currency found in the database.');
            return;
        }

        $defaultCode = $defaultCurrency->code;

        // ECB 全部是相对 EUR
        // 如果 EUR 是默认货币，直接写入 ECB 汇率即可
        if ($defaultCode === 'EUR') {
            $eur = $service->getCurrencyByCode('EUR');
            if ($eur) {
                $eur->exchange_rate = 1.0;
                $eur->save();
            }

            $updated = 0;

            foreach ($cube->Cube as $rateNode) {
                $currencyCode = (string) $rateNode['currency'];
                $rate = (float) $rateNode['rate'];

                $currency = $service->getCurrencyByCode($currencyCode);
                if ($currency) {
                    $currency->exchange_rate = $rate;
                    $currency->save();
                    $updated++;
                }
            }

            $service->clearCurrenciesCache();
            $this->info("Updated rates for {$updated} currencies relative to EUR.");

        } else {
            /**
             * 如果默认不是 EUR，例如默认是 USD
             * 则需要把 ECB 的汇率先转换为相对 USD
             *
             * 例如：
             * EUR → USD = 1 / rate(USD)
             * 所有其他币种 rate = ECB_rate / rate(USD)
             */

            // 找到 EUR → USD 的汇率
            $eurToDefault = null;
            foreach ($cube->Cube as $rateNode) {
                if ((string) $rateNode['currency'] === $defaultCode) {
                    $eurToDefault = (float) $rateNode['rate'];
                    break;
                }
            }

            if (! $eurToDefault) {
                $this->error("Default currency [{$defaultCode}] not found in ECB rates.");
                return;
            }

            // EUR 对默认币汇率
            $eurToDefaultRate = 1 / $eurToDefault;

            $eur = $service->getCurrencyByCode('EUR');
            if ($eur) {
                $eur->exchange_rate = $eurToDefaultRate;
                $eur->save();
            }

            $updated = 0;

            foreach ($cube->Cube as $rateNode) {
                $currencyCode = (string) $rateNode['currency'];
                $rate = (float) $rateNode['rate'];

                $currency = $service->getCurrencyByCode($currencyCode);
                if (!$currency) {
                    continue;
                }

                if ($currencyCode === $defaultCode) {
                    $currency->exchange_rate = 1.0;
                    $currency->save();
                    continue;
                }

                $convertedRate = $rate / $eurToDefault;
                $currency->exchange_rate = $convertedRate;
                $currency->save();
                $updated++;
            }

            $service->clearCurrenciesCache();
            $this->info("Updated rates for {$updated} currencies relative to {$defaultCode}.");
        }
    }
}