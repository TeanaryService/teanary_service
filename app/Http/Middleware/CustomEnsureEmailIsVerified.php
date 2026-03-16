<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified as Middleware;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
     * @param  Request  $request
     * @param  string|null  $redirectToRoute
     * @return Response|RedirectResponse|null
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
