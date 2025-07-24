<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\EnsureEmailIsVerified as Middleware;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Redirect;

class CustomEnsureEmailIsVerified extends Middleware
{
    public static function redirectTo($route)
    {
        return locaRoute('verification.notice');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (
            ! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                ! $request->user()->hasVerifiedEmail())
        ) {
            return $request->expectsJson()
                ? abort(403)
                : Redirect::guest(locaRoute($redirectToRoute ?: 'verification.notice'));
        }

        return $next($request);
    }
}
