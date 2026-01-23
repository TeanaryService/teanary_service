<?php

namespace App\Livewire;

use App\Models\Order;
use App\Notifications\OrderQueryVerificationCode;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class OrderQuery extends Component
{
    public $step = 1; // 1: 输入订单号/邮箱, 2: 输入验证码, 3: 显示订单详情

    public $orderNoOrEmail = '';

    public $verificationCode = '';

    public $order = null;

    public $errorMessage = '';

    public $countdown = 0; // 倒计时秒数

    protected $rules = [
        'orderNoOrEmail' => 'required|string|max:255',
        'verificationCode' => 'required|string|size:6',
    ];

    protected $messages = [
        'orderNoOrEmail.required' => 'orders.query_order_no_or_email_required',
        'verificationCode.required' => 'orders.query_verification_code_required',
        'verificationCode.size' => 'orders.query_verification_code_size',
    ];

    public function mount()
    {
        // 检查是否有缓存的验证码倒计时
        $cacheKey = $this->getCountdownCacheKey();
        $remaining = Cache::get($cacheKey, 0);
        if ($remaining > 0) {
            $this->countdown = $remaining;
            $this->step = 2;
            // 恢复订单号或邮箱
            $this->orderNoOrEmail = Cache::get($this->getOrderNoOrEmailCacheKey(), '');
        }
    }

    public function sendVerificationCode()
    {
        $this->validate([
            'orderNoOrEmail' => 'required|string|max:255',
        ], [], [
            'orderNoOrEmail' => __('orders.query_order_no_or_email'),
        ]);

        $this->errorMessage = '';

        // 查找订单
        $order = $this->findOrder();

        if (! $order) {
            $this->errorMessage = __('orders.query_order_not_found');

            return;
        }

        // 获取收货地址的邮箱
        $email = $order->shippingAddress?->email;

        if (! $email) {
            $this->errorMessage = __('orders.query_no_email_found');

            return;
        }

        // 生成6位数字验证码
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 存储验证码到缓存，有效期10分钟
        $cacheKey = $this->getVerificationCodeCacheKey($order->id);
        Cache::put($cacheKey, $code, now()->addMinutes(10));

        // 存储订单ID和邮箱到缓存
        Cache::put($this->getOrderIdCacheKey(), $order->id, now()->addMinutes(10));
        Cache::put($this->getOrderNoOrEmailCacheKey(), $this->orderNoOrEmail, now()->addMinutes(10));

        // 设置发送验证码的倒计时（60秒）
        $countdownKey = $this->getCountdownCacheKey();
        Cache::put($countdownKey, 60, now()->addSeconds(60));

        // 发送验证码邮件
        try {
            // 创建一个临时对象用于发送邮件
            $notifiable = new class
            {
                use \Illuminate\Notifications\Notifiable;

                public $email;

                public function routeNotificationForMail()
                {
                    return $this->email;
                }

                public function getKey()
                {
                    return $this->email;
                }
            };
            $notifiable->email = $email;

            $notifiable->notify(new OrderQueryVerificationCode($code, $order->order_no));

            $this->step = 2;
            $this->countdown = 60;
            $this->errorMessage = '';
            session()->flash('success', __('orders.query_verification_code_sent', ['email' => $this->maskEmail($email)]));
        } catch (\Exception $e) {
            $this->errorMessage = __('orders.query_send_code_failed');
            Log::error('发送订单查询验证码失败', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function verifyCode()
    {
        $this->validate([
            'verificationCode' => 'required|string|size:6',
        ], [], [
            'verificationCode' => __('orders.query_verification_code'),
        ]);

        $this->errorMessage = '';

        // 获取缓存的订单ID
        $orderId = Cache::get($this->getOrderIdCacheKey());

        if (! $orderId) {
            $this->errorMessage = __('orders.query_session_expired');
            $this->step = 1;

            return;
        }

        // 获取缓存的验证码
        $cacheKey = $this->getVerificationCodeCacheKey($orderId);
        $cachedCode = Cache::get($cacheKey);

        if (! $cachedCode || $cachedCode !== $this->verificationCode) {
            $this->errorMessage = __('orders.query_verification_code_invalid');

            return;
        }

        // 验证成功，加载订单详情
        $this->loadOrder($orderId);

        // 清除验证码缓存
        Cache::forget($cacheKey);
        Cache::forget($this->getOrderIdCacheKey());
        Cache::forget($this->getCountdownCacheKey());
        Cache::forget($this->getOrderNoOrEmailCacheKey());

        $this->step = 3;
    }

    public function resendCode()
    {
        if ($this->countdown > 0) {
            return;
        }

        $this->sendVerificationCode();
    }

    protected function findOrder(): ?Order
    {
        // 先尝试按订单号查找
        $order = Order::with('shippingAddress')->where('order_no', $this->orderNoOrEmail)->first();

        if ($order) {
            return $order;
        }

        // 再尝试按邮箱查找（通过收货地址）
        $order = Order::with('shippingAddress')->whereHas('shippingAddress', function ($query) {
            $query->where('email', $this->orderNoOrEmail);
        })->orderBy('created_at', 'desc')->first();

        return $order;
    }

    protected function loadOrder($orderId)
    {
        $localeService = app(LocaleCurrencyService::class);
        $lang = $localeService->getLanguageByCode(session('lang'));

        $this->order = Order::with([
            'orderItems.product.productTranslations',
            'orderItems.product.media',
            'orderShipments',
            'orderItems.productVariant.specificationValues.specificationValueTranslations',
            'orderItems.productVariant.media',
            'shippingAddress.country.countryTranslations',
            'shippingAddress.zone.zoneTranslations',
            'billingAddress',
            'currency',
        ])->findOrFail($orderId);
    }

    protected function getVerificationCodeCacheKey($orderId): string
    {
        return "order_query_verification_code_{$orderId}_".session()->getId();
    }

    protected function getOrderIdCacheKey(): string
    {
        return 'order_query_order_id_'.session()->getId();
    }

    protected function getCountdownCacheKey(): string
    {
        return 'order_query_countdown_'.session()->getId();
    }

    protected function getOrderNoOrEmailCacheKey(): string
    {
        return 'order_query_order_no_or_email_'.session()->getId();
    }

    protected function maskEmail($email): string
    {
        if (! $email) {
            return '';
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $username = $parts[0];
        $domain = $parts[1];

        if (strlen($username) <= 2) {
            $maskedUsername = str_repeat('*', strlen($username));
        } else {
            $maskedUsername = substr($username, 0, 1).str_repeat('*', strlen($username) - 2).substr($username, -1);
        }

        return $maskedUsername.'@'.$domain;
    }

    public function render()
    {
        $localeService = app(LocaleCurrencyService::class);
        $lang = $localeService->getLanguageByCode(session('lang'));

        return view('livewire.order-query', [
            'lang' => $lang,
            'localeService' => $localeService,
        ]);
    }
}
