<?php

namespace Vergatan10\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'description',
        'meta',
        'related_wallet_id',
        'status',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function relatedWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id');
    }
}
