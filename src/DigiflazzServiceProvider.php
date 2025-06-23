<?php

namespace Triyatna\Digiflazz;

use Illuminate\Support\ServiceProvider;
use Triyatna\Digiflazz\Commands\InstallCommand;
use Triyatna\Digiflazz\Services\DigiflazzClient;

class DigiflazzServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/digiflazz.php', 'digiflazz');

        $this->app->singleton(DigiflazzClient::class, function ($app) {
            $config = $app['config']['digiflazz'];
            throw_if(
                empty($config['username']) || empty($config['api_key']),
                new \Exception('Digiflazz credentials are not set in your .env file.')
            );
            return new DigiflazzClient($config['username'], $config['api_key']);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/digiflazz.php' => config_path('digiflazz.php'),
            ], 'digiflazz-config');

            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
