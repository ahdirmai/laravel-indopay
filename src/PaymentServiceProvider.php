<?php

namespace IndoPay;

use Illuminate\Support\ServiceProvider;
use IndoPay\Services\TransactionRecorder;
use IndoPay\Services\PaymentService;
use IndoPay\Services\InvoiceService;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/indopay.php', 'indopay');

        $this->app->singleton(TransactionRecorder::class);
        $this->app->singleton(PaymentService::class);
        $this->app->bind('indopay.invoice', InvoiceService::class); // For facade/alias

        // Register drivers
        // Drivers are usually resolved when needed, but we can bind them if we want singleton or logic.
        // WebhookController resolves them manually for now, or we can use a Manager.
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'indopay');
        
        $this->publishes([
            __DIR__.'/../config/indopay.php' => config_path('indopay.php'),
        ], 'indopay-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/indopay'),
        ], 'indopay-views');
    }
}
