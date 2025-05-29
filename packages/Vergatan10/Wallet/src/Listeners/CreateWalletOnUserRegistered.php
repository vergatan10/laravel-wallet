<?php

namespace Vergatan10\Wallet\Listeners;

use Illuminate\Auth\Events\Registered;
use Vergatan10\Wallet\Models\Wallet;

class CreateWalletOnUserRegistered
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Cek dulu biar tidak double create
        if (!Wallet::where('user_id', $user->id)->exists()) {
            $balance = config('wallet.default_balance', 0);
            Wallet::create([
                'user_id' => $user->id,
                'balance' => $balance,
            ]);
        }
    }
}
