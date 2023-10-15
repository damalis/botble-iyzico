<?php

namespace Botble\Iyzico\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;

class IyzicoServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('payment')) {
            return;
        }

        $this->setNamespace('plugins/iyzico')
            ->loadHelpers()
            ->loadAndPublishViews()
            ->publishAssets()
            ->loadRoutes();

        $this->app->register(HookServiceProvider::class);
    }
}
