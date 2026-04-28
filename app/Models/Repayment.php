<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Repayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'farmer_id', 'operator_id',
        'commodity_kg', 'commodity_rate_fcfa', 'total_fcfa_credited', 'notes',
    ];

    protected $casts = [
        'commodity_kg'          => 'decimal:3',
        'commodity_rate_fcfa'   => 'integer',
        'total_fcfa_credited'   => 'integer',
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

    public function debts(): BelongsToMany
    {
        return $this->belongsToMany(Debt::class, 'debt_repayment')
                    ->withPivot('amount_applied_fcfa')
                    ->withTimestamps();
    }

    /** Generate a unique reference like REP-20240101-0001 */
    public static function generateReference(): string
    {
        $prefix = 'REP-' . now()->format('Ymd');
        $lastToday = self::where('reference', 'like', $prefix . '%')->count();
        return $prefix . '-' . str_pad($lastToday + 1, 4, '0', STR_PAD_LEFT);
    }
}
