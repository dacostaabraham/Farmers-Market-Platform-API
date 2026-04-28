<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'farmer_id', 'operator_id', 'subtotal_fcfa', 'total_fcfa',
        'payment_method', 'interest_rate', 'interest_amount_fcfa', 'status', 'notes',
    ];

    protected $casts = [
        'subtotal_fcfa'        => 'integer',
        'total_fcfa'           => 'integer',
        'interest_rate'        => 'decimal:2',
        'interest_amount_fcfa' => 'integer',
    ];

    // ── Relationships ───────────────────────────────────────────────
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function debt(): HasOne
    {
        return $this->hasOne(Debt::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────
    public function isCreditTransaction(): bool
    {
        return $this->payment_method === 'credit';
    }

    /** Generate a unique reference like TXN-20240101-0001 */
    public static function generateReference(): string
    {
        $prefix = 'TXN-' . now()->format('Ymd');
        $lastToday = self::where('reference', 'like', $prefix . '%')->count();
        return $prefix . '-' . str_pad($lastToday + 1, 4, '0', STR_PAD_LEFT);
    }
}
