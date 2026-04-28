<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Farmer;
use Illuminate\Http\JsonResponse;

class DebtController extends Controller
{
    public function index(Farmer $farmer): JsonResponse
    {
        $debts = $farmer->debts()
                        ->with('transaction')
                        ->orderBy('created_at', 'asc')
                        ->get();

        return response()->json([
            'data' => [
                'farmer'              => $farmer->only(['id', 'identifier', 'full_name', 'phone']),
                'total_outstanding'   => $farmer->total_outstanding_debt,
                'available_credit'    => $farmer->available_credit,
                'credit_limit'        => $farmer->credit_limit_fcfa,
                'debts'               => $debts,
            ],
        ]);
    }

    public function show(Debt $debt): JsonResponse
    {
        return response()->json(['data' => $debt->load('transaction', 'farmer', 'repayments')]);
    }
}
