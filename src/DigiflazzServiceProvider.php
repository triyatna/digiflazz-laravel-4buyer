<?php

namespace Triyatna\DigiflazzBuyer;

use Illuminate\Support\ServiceProvider;
use Triyatna\DigiflazzBuyer\Http\Client\DigiflazzClient;
use Triyatna\DigiflazzBuyer\Http\Client\DigiflazzClientInterface;

class DigiflazzServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/digiflazz.php', 'digiflazz');

        $this->app->singleton(DigiflazzClientInterface::class, function ($app) {
            return new DigiflazzClient(
                baseUrl: config('digiflazz.base_url'),
                username: config('digiflazz.username'),
                apiKey: config('digiflazz.api_key'),
                timeout: (int) config('digiflazz.timeout'),
                retryTimes: (int) config('digiflazz.retry.times'),
                retrySleepMs: (int) config('digiflazz.retry.sleep_ms'),
            );
        });
    }

    public function boot(): void
    {
        $router = $this->app['router'];
        if (method_exists($router, 'aliasMiddleware')) {
            $router->aliasMiddleware('digiflazz.webhook', \Triyatna\DigiflazzBuyer\Http\Middleware\VerifyDigiflazzWebhook::class);
        }
        if ($this->app->runningInConsole()) {
            $this->commands([\Triyatna\DigiflazzBuyer\Console\InstallEnvCommand::class]);
        }

        $this->publishes([
            __DIR__.'/../config/digiflazz.php' => config_path('digiflazz.php'),
        ], 'digiflazz-config');
    }
}
