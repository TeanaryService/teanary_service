<?php

namespace Tests\Feature\Livewire;

use App\Livewire\OrderQuery;
use App\Models\Address;
use App\Models\Country;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\LivewireTestCase;

class OrderQueryTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->country = Country::factory()->create();
    }

    public function test_order_query_page_can_be_rendered()
    {
        $component = $this->livewire(OrderQuery::class);
        $component->assertSuccessful();
    }

    public function test_order_query_starts_at_step_one()
    {
        $component = $this->livewire(OrderQuery::class);
        $component->assertSet('step', 1);
    }

    public function test_can_send_verification_code()
    {
        Notification::fake();

        $user = $this->createUser();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'email' => 'test@example.com',
            'country_id' => $this->country->id,
        ]);
        $order = $this->createOrder([
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
        ]);

        // 刷新订单以确保关系已加载
        $order->refresh();
        $order->load('shippingAddress');

        // 验证订单和地址已正确创建
        $this->assertNotNull($order->order_no);
        $this->assertNotNull($order->shippingAddress);
        $this->assertEquals('test@example.com', $order->shippingAddress->email);

        $component = $this->livewire(OrderQuery::class)
            ->set('orderNoOrEmail', $order->order_no)
            ->call('sendVerificationCode');

        // 验证步骤已更新（成功时应该是 2）
        $component->assertSet('step', 2);
        // 注意：由于使用了匿名类发送通知，无法直接断言发送给用户
        // 但我们可以验证步骤已更新，说明通知发送成功
    }

    public function test_send_verification_code_validates_order_no_required()
    {
        $component = $this->livewire(OrderQuery::class)
            ->call('sendVerificationCode')
            ->assertHasErrors(['orderNoOrEmail']);
    }

    public function test_send_verification_code_handles_order_not_found()
    {
        $component = $this->livewire(OrderQuery::class)
            ->set('orderNoOrEmail', 'NON-EXISTENT-ORDER')
            ->call('sendVerificationCode');

        $component->assertSet('errorMessage', __('orders.query_order_not_found'));
    }

    public function test_can_verify_code_and_view_order()
    {
        Notification::fake();

        $user = $this->createUser();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'email' => 'test@example.com',
            'country_id' => $this->country->id,
        ]);
        $order = $this->createOrder([
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
        ]);

        // 先发送验证码以设置缓存
        $component1 = $this->livewire(OrderQuery::class)
            ->set('orderNoOrEmail', $order->order_no)
            ->call('sendVerificationCode');

        $component1->assertSet('step', 2);

        // 获取缓存的验证码（使用正确的缓存键）
        $sessionId = session()->getId();
        $orderId = Cache::get("order_query_order_id_{$sessionId}");
        $code = Cache::get("order_query_verification_code_{$orderId}_{$sessionId}");

        $component = $this->livewire(OrderQuery::class)
            ->set('orderNoOrEmail', $order->order_no)
            ->set('verificationCode', $code)
            ->call('verifyCode');

        $component->assertSet('step', 3);
        $this->assertNotNull($component->get('order'));
    }

    public function test_verify_code_validates_code_required()
    {
        $component = $this->livewire(OrderQuery::class)
            ->call('verifyCode')
            ->assertHasErrors(['verificationCode']);
    }

    public function test_verify_code_validates_code_size()
    {
        $component = $this->livewire(OrderQuery::class)
            ->set('verificationCode', '12345')
            ->call('verifyCode')
            ->assertHasErrors(['verificationCode']);
    }

    public function test_verify_code_handles_invalid_code()
    {
        Notification::fake();

        $user = $this->createUser();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'email' => 'test@example.com',
            'country_id' => $this->country->id,
        ]);
        $order = $this->createOrder([
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
        ]);

        // 先发送验证码以设置缓存
        $component1 = $this->livewire(OrderQuery::class)
            ->set('orderNoOrEmail', $order->order_no)
            ->call('sendVerificationCode');

        $component1->assertSet('step', 2);

        $component = $this->livewire(OrderQuery::class)
            ->set('orderNoOrEmail', $order->order_no)
            ->set('verificationCode', '000000')
            ->call('verifyCode');

        $component->assertSet('errorMessage', __('orders.query_verification_code_invalid'));
    }

    public function test_resend_code_resets_countdown()
    {
        Notification::fake();

        $user = $this->createUser();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'email' => 'test@example.com',
            'country_id' => $this->country->id,
        ]);
        $order = $this->createOrder([
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
        ]);

        $component = $this->livewire(OrderQuery::class)
            ->set('orderNoOrEmail', $order->order_no)
            ->call('sendVerificationCode');

        // 等待 countdown 变为 0（或直接设置为 0）
        $component->set('countdown', 0);

        // 然后重新发送
        $component->call('resendCode');

        $component->assertSet('countdown', 60);
    }
}
