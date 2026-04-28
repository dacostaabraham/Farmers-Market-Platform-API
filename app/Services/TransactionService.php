<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a transaction with items, apply interest for credit,
     * enforce credit limit, and create a debt record if needed.
     *
     * @param  Farmer  $farmer
     * @param  User    $operator
     * @param  array   $items      [['product_id' => 1, 'quantity' => 2], ...]
     * @param  string  $paymentMethod  'cash' | 'credit'
     * @param  string|null $notes
     * @return Transaction
     * @throws \Exception
     */
    public function createTransaction(
        Farmer $farmer,
        User $operator,
        array $items,
        string $paymentMethod,
        ?string $notes = null
    ): Transaction {
        return DB::transaction(function () use ($farmer, $operator, $items, $paymentMethod, $notes) {
            // 1. Build items and calculate subtotal
            $lineItems   = [];
            $subtotal    = 0;

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->is_active) {
                    throw new \Exception("Product '{$product->name}' is not available.");
                }

                $qty       = (int) $item['quantity'];
                $lineTotal = $product->price_fcfa * $qty;
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'unit_price_fcfa' => $product->price_fcfa,
                    'quantity'        => $qty,
                    'line_total_fcfa' => $lineTotal,
                ];
            }

            // 2. Calculate total (apply interest for credit)
            $interestRate       = 0.0;
            $interestAmountFcfa = 0;
            $totalFcfa          = $subtotal;

            if ($paymentMethod === 'credit') {
                $interestRate       = Setting::creditInterestRate(); // e.g. 30
                $interestAmountFcfa = (int) round($subtotal * ($interestRate / 100));
                $totalFcfa          = $subtotal + $interestAmountFcfa;

                // 3. Credit limit enforcement
                if (!$farmer->canTakeCredit($totalFcfa)) {
                    $outstanding = $farmer->total_outstanding_debt;
                    $available   = $farmer->available_credit;
                    throw new \Exception(
                        "Credit limit exceeded. Farmer's outstanding debt: {$outstanding} FCFA. " .
                        "Available credit: {$available} FCFA. " .
                        "Transaction amount: {$totalFcfa} FCFA."
                    );
                }
            }

            // 4. Persist transaction
            $transaction = Transaction::create([
                'reference'            => Transaction::generateReference(),
                'farmer_id'            => $farmer->id,
                'operator_id'          => $operator->id,
                'subtotal_fcfa'        => $subtotal,
                'total_fcfa'           => $totalFcfa,
                'payment_method'       => $paymentMethod,
                'interest_rate'        => $paymentMethod === 'credit' ? $interestRate : null,
                'interest_amount_fcfa' => $interestAmountFcfa,
                'notes'                => $notes,
            ]);

            // 5. Persist line items
            foreach ($lineItems as $lineItem) {
                TransactionItem::create(array_merge($lineItem, ['transaction_id' => $transaction->id]));
            }

            // 6. Create debt record for credit transactions
            if ($paymentMethod === 'credit') {
                Debt::create([
                    'farmer_id'             => $farmer->id,
                    'transaction_id'        => $transaction->id,
                    'original_amount_fcfa'  => $totalFcfa,
                    'remaining_amount_fcfa' => $totalFcfa,
                    'status'                => 'open',
                ]);
            }

            return $transaction->load('items', 'farmer', 'operator', 'debt');
        });
    }
}
