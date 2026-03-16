<?php

namespace Tests\Feature\Livewire\Manager;

use App\Livewire\Manager\Notifications;
use App\Notifications\OrderCancelledNotification;
use Tests\Feature\LivewireTestCase;

class ManagerNotificationsTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_notifications_page_can_be_rendered()
    {
        $component = $this->livewire(Notifications::class);
        $component->assertSuccessful();
    }

    public function test_notifications_displays_manager_notifications()
    {
        $manager = auth()->guard('manager')->user();

        // 创建通知
        $manager->notify(new OrderCancelledNotification(
            $this->createOrder()
        ));

        $component = $this->livewire(Notifications::class);

        $notifications = $component->get('notifications');
        $this->assertGreaterThan(0, $notifications->count());
    }

    public function test_notifications_mark_all_as_read_on_mount()
    {
        $manager = auth()->guard('manager')->user();

        // 创建未读通知
        $manager->notify(new OrderCancelledNotification(
            $this->createOrder()
        ));

        // 确保有未读通知
        $this->assertGreaterThan(0, $manager->unreadNotifications->count());

        $component = $this->livewire(Notifications::class);

        // 验证所有通知已标记为已读（mount 方法中会自动标记）
        $manager->refresh();
        $this->assertEquals(0, $manager->unreadNotifications->count());
    }

    public function test_manager_can_mark_notification_as_read()
    {
        $manager = auth()->guard('manager')->user();

        $manager->notify(new OrderCancelledNotification(
            $this->createOrder()
        ));

        // 获取通知并标记为未读
        $notification = $manager->notifications()->first();
        if ($notification) {
            $notification->markAsUnread();
        }

        $component = $this->livewire(Notifications::class)
            ->call('markAsRead', $notification->id);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_manager_can_mark_all_as_read()
    {
        $manager = auth()->guard('manager')->user();

        // 创建多个未读通知
        for ($i = 0; $i < 3; ++$i) {
            $manager->notify(new OrderCancelledNotification(
                $this->createOrder()
            ));
        }

        // 标记所有为未读（因为 mount 会标记所有为已读）
        $manager->notifications()->update(['read_at' => null]);
        $manager->refresh();

        $component = $this->livewire(Notifications::class)
            ->call('markAllAsRead');

        $manager->refresh();
        $this->assertEquals(0, $manager->unreadNotifications->count());
    }

    public function test_manager_can_delete_notification()
    {
        $manager = auth()->guard('manager')->user();

        $manager->notify(new OrderCancelledNotification(
            $this->createOrder()
        ));

        $notification = $manager->notifications()->first();
        $notificationId = $notification->id;

        $component = $this->livewire(Notifications::class)
            ->call('deleteNotification', $notificationId);

        $this->assertDatabaseMissing('notifications', ['id' => $notificationId]);
    }
}
