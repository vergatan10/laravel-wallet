<?php

namespace Vergatan10\Wallet;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Vergatan10\Wallet\Http\Middleware\EnsureWalletOwner;
use Vergatan10\Wallet\Services\WalletService;
use Illuminate\Auth\Events\Registered;
use Vergatan10\Wallet\Listeners\CreateWalletOnUserRegistered;
use Illuminate\Support\Facades\Event;

class WalletServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->app->make(Router::class)->aliasMiddleware('wallet.owner', EnsureWalletOwner::class);
        $this->app->make(\Illuminate\Routing\Router::class)->aliasMiddleware(
            'wallet.owner',
            \Vergatan10\Wallet\Http\Middleware\EnsureWalletOwner::class
        );
        Event::listen(Registered::class, CreateWalletOnUserRegistered::class);

        $this->publishes([
            __DIR__ . '/config/wallet.php' => config_path('wallet.php'),
        ], 'wallet-config');
    }

    public function register()
    {
        // $this->mergeConfigFrom(__DIR__ . '/config/wallet.php', 'wallet');
        $this->app->singleton('wallet-service', function ($app) {
            return new WalletService();
        });
    }
}
