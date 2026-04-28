<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'product_id', 'product_name', 'unit_price_fcfa', 'quantity', 'line_total_fcfa',
    ];

    protected $casts = [
        'unit_price_fcfa' => 'integer',
        'quantity'        => 'integer',
        'line_total_fcfa' => 'integer',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
