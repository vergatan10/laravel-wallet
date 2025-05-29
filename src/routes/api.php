<?php

use Illuminate\Support\Facades\Route;
use Vergatan10\Wallet\Http\Controllers\WalletController;

Route::prefix('api/wallet')
    ->middleware(['api', 'auth:sanctum', 'wallet.owner'])
    ->group(
        function () {
            Route::get('{wallet}', [WalletController::class, 'show']); // saldo
            Route::get('{wallet}/transactions', [WalletController::class, 'transactions']); // riwayat
            Route::post('{wallet}/deposit', [WalletController::class, 'deposit']);
            Route::post('{wallet}/withdraw', [WalletController::class, 'withdraw']);
            Route::post('{wallet}/transfer', [WalletController::class, 'transfer']);
            Route::post('{wallet}/transactions/{transaction}/refund', [WalletController::class, 'refund']);
            // Route::post('{wallet}/lock', [WalletController::class, 'lock']);
            // Route::post('{wallet}/unlock', [WalletController::class, 'unlock']);
        }
    );
