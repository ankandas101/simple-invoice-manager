<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        try {
            if (env('APP_INSTALLED') && function_exists('get_settings')) {
                try {
                    $settings = get_settings();
                    $settings['timezone'] ??= 'UTC';
                    config(['app.timezone' => $settings['timezone']]);
                    Carbon::setLocale($settings['default_locale'] ?? 'en');

                    $dot_settings = get_dot_keys(true);
                    foreach ($dot_settings as &$value) {
                        $value = $settings[$value] ?? null;
                    }

                    config($dot_settings);
                } catch (\Exception $e) {
                    logger()->error('Provider settings error: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            logger()->error('Provider settings failed! ' . $e->getMessage());
        }
    }

    public function register()
    {
        $this->app->extend(
            \Illuminate\Translation\Translator::class,
            fn ($translator) => new \App\Helpers\Translator($translator->getLoader(), $translator->getLocale())
        );
    }
}
