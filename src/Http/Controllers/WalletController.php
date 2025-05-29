<?php

namespace Vergatan10\Wallet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Vergatan10\Wallet\Models\Wallet;
use Vergatan10\Wallet\Services\WalletService;
use Vergatan10\Wallet\Models\Transaction;
use Exception;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function deposit(Request $request, Wallet $wallet)
    {
        try {
            $data = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string',
            ]);

            $tx = $this->walletService->deposit($wallet, $data['amount'], $data['description'] ?? '');

            return response()->json(['message' => 'Deposit success', 'transaction' => $tx]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Deposit failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function withdraw(Request $request, Wallet $wallet)
    {
        try {
            $data = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string',
                'meta' => 'nullable|array',
                'meta.*' => 'string',
            ]);

            $tx = $this->walletService->withdraw($wallet, $data['amount'], $data['description'] ?? '', $data['meta'] ?? []);
            if (!$tx) {
                return response()->json(['message' => 'Withdraw failed'], 500);
            }

            return response()->json(['message' => 'Withdraw success', 'transaction' => $tx]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Withdraw failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function transfer(Request $request, Wallet $fromWallet)
    {
        try {
            $data = $request->validate([
                'to_wallet_id' => 'required|exists:wallets,id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string',
                'meta' => 'nullable|array',
                'meta.*' => 'string',
            ]);

            $toWallet = Wallet::findOrFail($data['to_wallet_id']);

            [$withdrawTx, $depositTx] = $this->walletService->transfer($fromWallet, $toWallet, $data['amount'], $data['description'] ?? '', $data['meta'] ?? []);
            if (!$withdrawTx || !$depositTx) {
                return response()->json(['message' => 'Transfer failed'], 500);
            }

            return response()->json([
                'message' => 'Transfer success',
                'withdraw' => $withdrawTx,
                'deposit' => $depositTx,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Transfer failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Wallet $wallet)
    {
        try {
            return response()->json([
                'balance' => $wallet->balance,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to get wallet balance', 'error' => $e->getMessage()], 500);
        }
    }

    public function transactions(Wallet $wallet)
    {
        try {
            $transactions = $wallet->transactions()->latest()->paginate(10);

            return response()->json($transactions);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to get transactions', 'error' => $e->getMessage()], 500);
        }
    }

    public function lock(Wallet $wallet)
    {
        try {
            $wallet->update(['is_locked' => true]);
            return response()->json(['message' => 'Wallet locked']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to lock wallet', 'error' => $e->getMessage()], 500);
        }
    }

    public function unlock(Wallet $wallet)
    {
        try {
            $wallet->update(['is_locked' => false]);
            return response()->json(['message' => 'Wallet unlocked']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to unlock wallet', 'error' => $e->getMessage()], 500);
        }
    }

    public function refund(Wallet $wallet, Transaction $transaction)
    {
        try {
            if ($transaction->wallet_id !== $wallet->id) {
                return response()->json(['message' => 'Transaction not in this wallet'], 403);
            }

            $refunded = $this->walletService->refund($transaction);

            return response()->json($refunded);
        } catch (Exception $e) {
            return response()->json(['message' => 'Refund failed', 'error' => $e->getMessage()], 500);
        }
    }
}
