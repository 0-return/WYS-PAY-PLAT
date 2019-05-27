<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Tymon\JWTAuth\Facades\JWTAuth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //接口权限
        Blade::directive('abc', function ($expression) {
            $arr = explode(',', $expression);
            if ($arr[0] == 'user') {
                $user = User::where('id', $arr[0])->first();
                $data = $user->hasPermissionTo('1536025359');
               dd($data);
                return $data;
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
