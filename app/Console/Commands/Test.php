<?php

namespace App\Console\Commands;

use App\Enums\PaymentMethodEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Address;
use App\Models\ArticleTranslation;
use App\Models\CountryTranslation;
use App\Models\Order;
use App\Models\ZoneTranslation;
use App\Services\PaymentService;
use App\Services\PromotionService;
use App\Services\SearchEnginePushService;
use App\Services\ShippingService;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $push = app(SearchEnginePushService::class);
        dd($push->push('https://teanary.com'));

        ArticleTranslation::whereIn('article_id', [22, 23, 24, 25,26, 27, 28, 29, 30])->chunk(100, function ($translations) {
            foreach ($translations as $translation) {
                $content = Str::replace('stronge', 'storage', $translation->content);
                $translation->update(['content' => $content]);
            }
        });

        dd(123);

        // $order = Order::first();
        // $order->name = config('app.name') . __('app.order_items');
        // $paymentMethod = PaymentMethodEnum::fromValue('paypal');
        // $redirectUrl = app(PaymentService::class)->createPayment($paymentMethod, $order->toArray());
        // dd($redirectUrl);

        // $service = app(ShippingService::class);
        // $result = $service->getAvailableMethods(Address::first());
        // dd($result);

        // dd(1233);

        // $service = app(TranslationService::class);
        // $result = $service->translate('今天是个好天气', 'zh_CN', 'en_gb');
        // dd($result);

        // ZoneTranslation::where('language_id', 1)->chunk(100, function (Collection $zoneTranslations) use ($service) {
        //     foreach ($zoneTranslations as $zoneTranslation) {
        //         $result = $service->translate($zoneTranslation->name, 'en', 'ru');
        //         if (empty($result)) {
        //             continue;
        //         }
        //         $this->info($result);
        //         ZoneTranslation::create([
        //             'language_id' => 8,
        //             'zone_id' => $zoneTranslation->zone_id,
        //             'name' => $result,
        //             'created_at' => now(),
        //             'updated_at' => now()
        //         ]);
        //     }
        // });
        // dd('zone');

        // CountryTranslation::where('language_id', 1)->chunk(100, function (Collection $countryTranslations) use ($service) {
        //     foreach ($countryTranslations as $countryTranslation) {
        //         $result = $service->translate($countryTranslation->name, 'en', 'ru');
        //         $this->info($result);
        //         CountryTranslation::create([
        //             'language_id' => 8,
        //             'country_id' => $countryTranslation->country_id,
        //             'name' => $result,
        //             'created_at' => now(),
        //             'updated_at' => now()
        //         ]);
        //     }
        // });
        // dd('country');

        // $service = app(PromotionService::class);
        // dd($service->getAvailablePromotions()->toArray());

        $email = 'xcalder@foxmail.com';
        //
        Mail::raw('这是一封测试邮件，你的 Laravel 邮件系统已配置成功！', function ($message) use ($email) {
            $message->to($email)
                ->subject('Laravel 测试邮件');
        });

        $this->info("已向 {$email} 发送测试邮件！");
    }
}
