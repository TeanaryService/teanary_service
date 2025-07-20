<?php

namespace App\Console\Commands;

use App\Services\PromotionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
        $service = app(PromotionService::class);
        dd($service->getAvailablePromotions()->toArray());

        $email = 'xcalder@foxmail.com';
        //
        Mail::raw('这是一封测试邮件，你的 Laravel 邮件系统已配置成功！', function ($message) use ($email) {
            $message->to($email)
                ->subject('Laravel 测试邮件');
        });

        $this->info("已向 {$email} 发送测试邮件！");
    }
}
