<?php

namespace Scriptoshi\LivewireTelegramAuth;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Scriptoshi\LivewireTelegramAuth\Components\TelegramLogin;

class LivewireTelegramAuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        Livewire::component('telegram-auth', TelegramLogin::class);
        // Publish configurations
        $this->publishes([
            __DIR__ . '/../config/telegram-auth.php' => config_path('telegram-auth.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/add_telegram_fields_to_users_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_add_telegram_fields_to_users_table.php'),
        ], 'migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'telegram-auth');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Merge configurations
        $this->mergeConfigFrom(
            __DIR__ . '/../config/telegram-auth.php',
            'telegram-auth'
        );
    }
}
