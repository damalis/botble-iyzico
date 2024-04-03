<?php

namespace Botble\Iyzico;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
	public static function remove(): void
    {
        Setting::query()
            ->whereIn('key', [
                'payment_iyzico_name',
                'payment_iyzico_description',
                'payment_iyzico_public',
                'payment_iyzico_secret',
                'payment_iyzico_mode',
                'payment_iyzico_status',
            ])
            ->delete();
    }
}
