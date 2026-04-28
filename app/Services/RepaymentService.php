<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Repayment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RepaymentService
{
    /**
     * Record a commodity repayment and apply it using FIFO debt settlement.
     *
     * @param  Farmer  $farmer
     * @param  User    $operator
     * @param  float   $commodityKg  — kg of commodity received
     * @param  string|null $notes
     * @return Repayment
     * @throws \Exception
     */
    public function recordRepayment(
        Farmer $farmer,
        User $operator,
        float $commodityKg,
        ?string $notes = null
    ): Repayment {
        return DB::transaction(function () use ($farmer, $operator, $commodityKg, $notes) {
            $commodityRate    = Setting::commodityRateFcfa();
            $totalFcfaCredited = (int) round($commodityKg * $commodityRate);

            if ($totalFcfaCredited <= 0) {
                throw new \Exception('Repayment amount must be greater than zero.');
            }

            // Fetch open debts ordered by created_at (FIFO)
            $openDebts = Debt::where('farmer_id', $farmer->id)
                             ->whereIn('status', ['open', 'partially_paid'])
                             ->orderBy('created_at', 'asc')
                             ->lockForUpdate()
                             ->get();

            if ($openDebts->isEmpty()) {
                throw new \Exception('Farmer has no outstanding debts.');
            }

            // Create repayment record
            $repayment = Repayment::create([
                'reference'            => Repayment::generateReference(),
                'farmer_id'            => $farmer->id,
                'operator_id'          => $operator->id,
                'commodity_kg'         => $commodityKg,
                'commodity_rate_fcfa'  => $commodityRate,
                'total_fcfa_credited'  => $totalFcfaCredited,
                'notes'                => $notes,
            ]);

            // Apply FIFO repayment
            $remaining = $totalFcfaCredited;

            foreach ($openDebts as $debt) {
                if ($remaining <= 0) break;

                $applied   = min($debt->remaining_amount_fcfa, $remaining);
                $remaining = $debt->applyPayment($remaining); // returns leftover

                // Record pivot
                $repayment->debts()->attach($debt->id, [
                    'amount_applied_fcfa' => $applied,
                ]);
            }

            return $repayment->load('debts', 'farmer', 'operator');
        });
    }
}
