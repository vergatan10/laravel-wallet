<?php

namespace Vergatan10\Wallet\Tests\Unit;

use Vergatan10\Wallet\Services\WalletService;
use Vergatan10\Wallet\Tests\TestCase;

class WalletServiceTest extends TestCase
{
    /** @test */
    public function it_has_get_balance_method()
    {
        $walletService = new WalletService();
        $this->assertTrue(method_exists($walletService, 'getBalance'), 'WalletService must have getBalance() method');
    }
}
