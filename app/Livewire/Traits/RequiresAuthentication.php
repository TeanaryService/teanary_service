<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * 提供认证检查功能的 Trait.
 *
 * 用于需要用户认证的组件
 */
trait RequiresAuthentication
{
    /**
     * 在组件挂载时检查认证.
     */
    public function bootRequiresAuthentication(): void
    {
        if (! Auth::check()) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * 检查用户是否已认证，如果未认证则抛出异常.
     *
     * @throws HttpException
     */
    protected function ensureAuthenticated(): void
    {
        if (! Auth::check()) {
            abort(403, 'Unauthorized');
        }
    }
}
