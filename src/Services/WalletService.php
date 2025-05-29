<?php

namespace Vergatan10\Wallet\Services;

use Illuminate\Support\Facades\DB;
use Vergatan10\Wallet\Models\Wallet;
use Vergatan10\Wallet\Models\Transaction;
use Exception;

class WalletService
{
    private function ensureWalletNotLocked(Wallet $wallet)
    {
        if ($wallet->is_locked) {
            throw new \Exception("Wallet is locked");
        }
    }

    /**
     * Tambah saldo ke wallet
     */
    public function deposit(Wallet $wallet, float $amount, string $description = '', array $meta = []): Transaction
    {
        try {
            $this->ensureWalletNotLocked($wallet);
            return DB::transaction(function () use ($wallet, $amount, $description, $meta) {
                $wallet->increment('balance', $amount);

                return $wallet->transactions()->create([
                    'type' => 'deposit',
                    'amount' => $amount,
                    'description' => $description,
                    'meta' => $meta,
                ]);
            }, 5);
        } catch (Exception $e) {
            throw new Exception("Deposit failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Kurangi saldo dari wallet
     */
    public function withdraw(Wallet $wallet, float $amount, string $description = '', array $meta = []): Transaction
    {
        try {
            $this->ensureWalletNotLocked($wallet);
            if ($wallet->balance < $amount) {
                throw new Exception('Insufficient balance');
            }

            return DB::transaction(function () use ($wallet, $amount, $description, $meta) {
                $wallet->decrement('balance', $amount);

                return $wallet->transactions()->create([
                    'type' => 'withdraw',
                    'amount' => $amount,
                    'description' => $description,
                    'meta' => $meta,
                ]);
            }, 5);
        } catch (Exception $e) {
            throw new Exception("Withdraw failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Transfer saldo antar wallet
     */
    public function transfer(Wallet $fromWallet, Wallet $toWallet, float $amount, string $description = '', array $meta = []): Transaction|array
    {
        try {
            $this->ensureWalletNotLocked($fromWallet);
            $this->ensureWalletNotLocked($toWallet);
            if ($fromWallet->balance < $amount) {
                throw new Exception('Insufficient balance for transfer');
            }

            return DB::transaction(function () use ($fromWallet, $toWallet, $amount, $description, $meta) {
                // Kurangi saldo pengirim
                $fromWallet->decrement('balance', $amount);
                // Buat transaksi di wallet pengirim
                if ($fromWallet->id === $toWallet->id) {
                    // Jika transfer ke wallet yang sama, cukup buat transaksi withdraw
                    return $fromWallet->transactions()->create([
                        'type' => 'withdraw',
                        'amount' => $amount,
                        'description' => $description ?: 'Transfer to self',
                        'meta' => $meta,
                    ]);
                }
                $withdrawTransaction = $fromWallet->transactions()->create([
                    'type' => 'transfer',
                    'amount' => $amount,
                    'description' => $description ?: 'Transfer to wallet #' . $toWallet->id,
                    'meta' => $meta,
                    'related_wallet_id' => $toWallet->id,
                ]);

                // Tambah saldo penerima
                $toWallet->increment('balance', $amount);
                // Buat transaksi di wallet penerima
                $depositTransaction = $toWallet->transactions()->create([
                    'type' => 'deposit',
                    'amount' => $amount,
                    'description' => $description ?: 'Transfer from wallet #' . $fromWallet->id,
                    'meta' => $meta,
                    'related_wallet_id' => $fromWallet->id,
                ]);
                return [$withdrawTransaction, $depositTransaction];
            }, 5);
        } catch (Exception $e) {
            throw new Exception("Transfer failed: " . $e->getMessage(), 0, $e);
        }
    }


    // public function transfer(Wallet $from, Wallet $to, float $amount, string $desc = ''): Transaction
    // {
    //     $this->ensureWalletNotLocked($from);
    //     $this->ensureWalletNotLocked($to);

    //     // Kurangi saldo pengirim, tandai sebagai pending
    //     if ($from->balance < $amount) {
    //         throw new Exception('Insufficient balance for transfer');
    //     }
    //     $from->decrement('balance', $amount);
    //     // Buat transaksi di wallet pengirim
    //     return $from->transactions()->create([
    //         'type' => 'transfer',
    //         'amount' => $amount,
    //         'description' => $desc,
    //         'status' => 'pending',
    //         'meta' => [
    //             'to_wallet_id' => $to->id
    //         ]
    //     ]);
    // }

    // public function confirmTransfer(Transaction $transaction): Transaction
    // {
    //     if ($transaction->status !== 'pending') {
    //         throw new \Exception("Transfer is not pending");
    //     }

    //     $toWallet = Wallet::find($transaction->meta['to_wallet_id']);
    //     $this->ensureWalletNotLocked($toWallet);

    //     // Tambah saldo penerima
    //     $toWallet->transactions()->create([
    //         'type' => 'deposit',
    //         'amount' => $transaction->amount,
    //         'description' => "Transfer from wallet #{$transaction->wallet_id}",
    //     ]);


    //     // Ubah status jadi complete
    //     $transaction->update(['status' => 'completed']);

    //     return $transaction;
    // }


    /**
     * Refund transaksi
     */
    public function refund(Transaction $transaction): Transaction
    {
        try {
            return DB::transaction(function () use ($transaction) {
                $wallet = $transaction->wallet;

                $this->ensureWalletNotLocked($wallet);

                // Cek apakah transaksi sudah pernah di-refund
                $existingRefund = Transaction::where('description', 'LIKE', "Refund for transaction #{$transaction->id}")
                    ->exists();

                if ($existingRefund) {
                    throw new \Exception("Transaction has already been refunded");
                }
                

                // Tentukan arah refund berdasarkan jenis transaksi
                $reverseType = match ($transaction->type) {
                    'deposit' => 'withdraw',
                    'withdraw', 'transfer' => 'deposit',
                    default => throw new \Exception("Cannot refund this transaction type"),
                };

                // Cek apakah saldo cukup untuk refund
                if ($wallet->balance < $transaction->amount) {
                    throw new \Exception("Insufficient balance for refund");
                }

                // Buat transaksi baru
                $refundTransaction = $wallet->transactions()->create([
                    'type' => $reverseType,
                    'amount' => $transaction->amount,
                    'description' => "Refund for transaction #{$transaction->id}",
                ]);

                if ($refundTransaction->type === 'deposit') {
                    $wallet->increment('balance', $refundTransaction->amount);
                } else {
                    $wallet->decrement('balance', $refundTransaction->amount);
                }

                return $refundTransaction;
            }, 5);
        } catch (Exception $e) {
            throw new Exception("Refund failed: " . $e->getMessage(), 0, $e);
        }
    }
}
