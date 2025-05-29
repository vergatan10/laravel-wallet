<?php

namespace Vergatan10\Wallet\Facades;

use Illuminate\Support\Facades\Facade;

class Wallet extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'wallet-service';
    }
}
