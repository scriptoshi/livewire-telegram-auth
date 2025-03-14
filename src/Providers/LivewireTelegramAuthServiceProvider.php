<?php

namespace Scriptoshi\LivewireTelegramAuth\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Scriptoshi\LivewireTelegramAuth\Components\TelegramLogin;

class LivewireTelegramAuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        
        // Register views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'telegram-auth');
        
        // Register Livewire components
        Livewire::component('telegram-login', TelegramLogin::class);
        
        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'telegram-auth-migrations');
        
        // Publish views
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/telegram-auth'),
        ], 'telegram-auth-views');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register any service bindings here if needed
    }
}
