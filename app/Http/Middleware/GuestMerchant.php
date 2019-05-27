<?php

namespace App\Http\Middleware;

use Closure;

class GuestMerchant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->guard('merchant')->check()) {
            return redirect('/merchant/index');
        }
        return $next($request);
    }
}
