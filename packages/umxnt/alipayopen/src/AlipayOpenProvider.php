<?php

namespace Alipayopen;

use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\AopEncrypt;
use Alipayopen\Sdk\Request\AlipayOfflineMarketShopCategoryQueryRequest;
use Illuminate\Support\ServiceProvider;

class AlipayOpenProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('AopClient', function () {
            return new AopClient();
        });//相当于其他地方就可以用app('AopClient')

        $this->app->singleton('AopEncrypt', function () {
            return new AopEncrypt();
        });//相当于其他地方就可以用app('AopClient')

        $this->app->singleton('AlipayOfflineMarketShopCategoryQueryRequest', function () {
            return new AlipayOfflineMarketShopCategoryQueryRequest();
        });
    }
}
