<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id', 'transaction_id', 'original_amount_fcfa', 'remaining_amount_fcfa', 'status', 'due_date',
    ];

    protected $casts = [
        'original_amount_fcfa'  => 'integer',
        'remaining_amount_fcfa' => 'integer',
        'due_date'              => 'datetime',
    ];

    // ── Relationships ───────────────────────────────────────────────
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function repayments(): BelongsToMany
    {
        return $this->belongsToMany(Repayment::class, 'debt_repayment')
                    ->withPivot('amount_applied_fcfa')
                    ->withTimestamps();
    }

    // ── Helpers ─────────────────────────────────────────────────────
    public function isFullyPaid(): bool
    {
        return $this->remaining_amount_fcfa === 0;
    }

    /** Apply a partial or full payment. Returns the leftover credit (if payment > debt). */
    public function applyPayment(int $amountFcfa): int
    {
        $applied = min($this->remaining_amount_fcfa, $amountFcfa);
        $this->remaining_amount_fcfa -= $applied;

        if ($this->remaining_amount_fcfa === 0) {
            $this->status = 'paid';
        } else {
            $this->status = 'partially_paid';
        }
        $this->save();

        return $amountFcfa - $applied; // leftover
    }
}
