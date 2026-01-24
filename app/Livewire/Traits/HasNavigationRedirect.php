<?php

namespace App\Livewire\Traits;

/**
 * 提供导航重定向功能的 Trait.
 *
 * 用于需要重定向并显示消息的组件
 */
trait HasNavigationRedirect
{
    /**
     * 重定向到指定路由并显示消息.
     *
     * @param  string  $route  路由名称
     * @param  string  $messageKey  消息键（默认 'created_successfully'）
     * @return \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
     */
    protected function redirectWithMessage(string $route, string $messageKey = 'created_successfully')
    {
        $this->dispatch('flash-message', type: 'success', message: __("app.{$messageKey}"));

        // 使用 Livewire 的 redirect 方法，自动支持智能导航
        return $this->redirect(locaRoute($route), navigate: true);
    }

    /**
     * 显示成功消息.
     *
     * @param  string  $messageKey  消息键（默认 'created_successfully'）
     */
    protected function flashMessage(string $messageKey = 'created_successfully'): void
    {
        $this->dispatch('flash-message', type: 'success', message: __("app.{$messageKey}"));
    }
}
