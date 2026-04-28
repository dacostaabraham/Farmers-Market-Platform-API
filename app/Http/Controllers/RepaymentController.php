<?php

namespace App\Http\Controllers;

use App\Http\Requests\Repayment\StoreRepaymentRequest;
use App\Models\Farmer;
use App\Models\Repayment;
use App\Models\Setting;
use App\Services\RepaymentService;
use Illuminate\Http\JsonResponse;

class RepaymentController extends Controller
{
    public function __construct(private RepaymentService $repaymentService) {}

    public function index(Farmer $farmer): JsonResponse
    {
        $repayments = $farmer->repayments()
                             ->with(['debts', 'operator'])
                             ->latest()
                             ->get();
        return response()->json(['data' => $repayments]);
    }

    public function store(StoreRepaymentRequest $request): JsonResponse
    {
        $farmer   = Farmer::findOrFail($request->farmer_id);
        $operator = $request->user();

        // Preview conversion before recording (useful for confirmation in app)
        if ($request->has('preview') && $request->preview) {
            $rate          = Setting::commodityRateFcfa();
            $fcfaEquivalent = (int) round($request->commodity_kg * $rate);
            return response()->json([
                'data' => [
                    'commodity_kg'      => $request->commodity_kg,
                    'rate_fcfa_per_kg'  => $rate,
                    'total_fcfa'        => $fcfaEquivalent,
                    'outstanding_debt'  => $farmer->total_outstanding_debt,
                ],
            ]);
        }

        try {
            $repayment = $this->repaymentService->recordRepayment(
                $farmer,
                $operator,
                (float) $request->commodity_kg,
                $request->notes
            );

            return response()->json([
                'message' => 'Repayment recorded successfully.',
                'data'    => $repayment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Repayment $repayment): JsonResponse
    {
        return response()->json(['data' => $repayment->load('debts', 'farmer', 'operator')]);
    }
}
