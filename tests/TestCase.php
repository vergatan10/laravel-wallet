<?php

namespace Vergatan10\Wallet\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Vergatan10\Wallet\WalletServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [WalletServiceProvider::class];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations(); // load default users table 
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
    }

    protected function defineDatabaseFactories()
    {
        $this->loadLaravelMigrations();
        $this->withFactories(__DIR__ . '/factories');
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set up in-memory SQLite for testing 
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
