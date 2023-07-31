<?php

namespace UpdateUseful\MessageHook;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Support\ServiceProvider;

class MessageHookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerDefaultSettingConfig();

        $this->commands(SendVersionMessageCommand::class);
    }

    protected function registerDefaultSettingConfig(): void
    {
        $source = realpath($raw = __DIR__.'/../config/MessageHook.php') ?: $raw;

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('MessageHook.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('MessageHook');
        }

        $this->mergeConfigFrom($source, 'MessageHook');
    }
}
