<?php

namespace Vergatan10\Wallet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Vergatan10\Wallet\Models\Wallet;
use Vergatan10\Wallet\Services\WalletService;
use Vergatan10\Wallet\Models\Transaction;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function deposit(Request $request, Wallet $wallet)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $tx = $this->walletService->deposit($wallet, $data['amount'], $data['description'] ?? '');

        return response()->json(['message' => 'Deposit success', 'transaction' => $tx]);
    }

    public function withdraw(Request $request, Wallet $wallet)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $tx = $this->walletService->withdraw($wallet, $data['amount'], $data['description'] ?? '');

        return response()->json(['message' => 'Withdraw success', 'transaction' => $tx]);
    }

    public function transfer(Request $request, Wallet $fromWallet)
    {
        $data = $request->validate([
            'to_wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $toWallet = Wallet::findOrFail($data['to_wallet_id']);

        [$withdrawTx, $depositTx] = $this->walletService->transfer($fromWallet, $toWallet, $data['amount'], $data['description'] ?? '');

        return response()->json([
            'message' => 'Transfer success',
            'withdraw' => $withdrawTx,
            'deposit' => $depositTx,
        ]);
    }

    public function show(Wallet $wallet)
    {
        return response()->json([
            'balance' => $wallet->balance,
        ]);
    }

    public function transactions(Wallet $wallet)
    {
        $transactions = $wallet->transactions()->latest()->paginate(10);

        return response()->json($transactions);
    }

    public function lock(Wallet $wallet)
    {
        $wallet->update(['is_locked' => true]);
        return response()->json(['message' => 'Wallet locked']);
    }

    public function unlock(Wallet $wallet)
    {
        $wallet->update(['is_locked' => false]);
        return response()->json(['message' => 'Wallet unlocked']);
    }

    public function refund(Wallet $wallet, Transaction $transaction)
    {
        if ($transaction->wallet_id !== $wallet->id) {
            return response()->json(['message' => 'Transaction not in this wallet'], 403);
        }

        $refunded = $this->walletService->refund($transaction);

        return response()->json($refunded);
    }
}
