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
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectWithMessage(string $route, string $messageKey = 'created_successfully'): \Illuminate\Http\RedirectResponse
    {
        session()->flash('message', __("app.{$messageKey}"));

        return redirect()->to(locaRoute($route), navigate: true);
    }

    /**
     * 显示成功消息.
     *
     * @param  string  $messageKey  消息键（默认 'created_successfully'）
     */
    protected function flashMessage(string $messageKey = 'created_successfully'): void
    {
        session()->flash('message', __("app.{$messageKey}"));
    }
}
