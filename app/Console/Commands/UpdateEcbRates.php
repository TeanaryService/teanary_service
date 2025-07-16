<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Currency;

class UpdateEcbRates extends Command
{
    protected $signature = 'rates:update-ecb';
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

        // 找到默认币种
        $defaultCurrency = Currency::where('default', true)->first();

        if (! $defaultCurrency) {
            $this->error('No default currency found in the database.');
            return;
        }

        $defaultCode = $defaultCurrency->code;

        // ECB 全部是相对 EUR
        // 如果 EUR 是默认货币，直接写入 ECB 汇率即可
        if ($defaultCode === 'EUR') {
            Currency::where('code', 'EUR')->update(['exchange_rate' => 1.0]);

            $updated = 0;

            foreach ($cube->Cube as $rateNode) {
                $currencyCode = (string) $rateNode['currency'];
                $rate = (float) $rateNode['rate'];

                Currency::where('code', $currencyCode)
                    ->update(['exchange_rate' => $rate]);

                $updated++;
            }

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

            // EUR 本身
            Currency::where('code', 'EUR')->update([
                'exchange_rate' => $eurToDefaultRate,
            ]);

            $updated = 0;

            foreach ($cube->Cube as $rateNode) {
                $currencyCode = (string) $rateNode['currency'];
                $rate = (float) $rateNode['rate'];

                if ($currencyCode === $defaultCode) {
                    Currency::where('code', $currencyCode)
                        ->update(['exchange_rate' => 1.0]);
                    continue;
                }

                $convertedRate = $rate / $eurToDefault;

                Currency::where('code', $currencyCode)
                    ->update(['exchange_rate' => $convertedRate]);

                $updated++;
            }

            $this->info("Updated rates for {$updated} currencies relative to {$defaultCode}.");
        }
    }
}