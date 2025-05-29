<?php

namespace Vergatan10\Wallet\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Vergatan10\Wallet\Models\Wallet;

class EnsureWalletOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $wallet = $request->route('wallet');

        if ($wallet instanceof Wallet && $wallet->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access to wallet'], 403);
        }

        return $next($request);
    }
}
