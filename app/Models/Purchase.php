<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'track_id',
        'amount_cents',
        'payment_id',
        'payment_method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function payout()
    {
        return $this->hasOne(Payout::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2, ',', ' ') . ' €';
    }
}