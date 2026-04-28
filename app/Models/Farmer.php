<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier', 'firstname', 'lastname', 'phone', 'village', 'credit_limit_fcfa', 'is_active',
    ];

    protected $casts = [
        'credit_limit_fcfa' => 'integer',
        'is_active'         => 'boolean',
    ];

    // ── Relationships ───────────────────────────────────────────────
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    // ── Computed Properties ─────────────────────────────────────────
    public function getFullNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /** Total outstanding debt in FCFA */
    public function getTotalOutstandingDebtAttribute(): int
    {
        return (int) $this->debts()
            ->whereIn('status', ['open', 'partially_paid'])
            ->sum('remaining_amount_fcfa');
    }

    /** Available credit in FCFA */
    public function getAvailableCreditAttribute(): int
    {
        return max(0, $this->credit_limit_fcfa - $this->total_outstanding_debt);
    }

    /** Check if farmer can take credit for a given amount */
    public function canTakeCredit(int $amountFcfa): bool
    {
        return ($this->total_outstanding_debt + $amountFcfa) <= $this->credit_limit_fcfa;
    }
}
