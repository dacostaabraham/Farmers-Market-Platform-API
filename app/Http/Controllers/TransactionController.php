<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Models\Farmer;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Transaction::with(['farmer', 'operator', 'items'])
                            ->latest();

        // Operators see only their own transactions
        if ($user->isOperator()) {
            $query->where('operator_id', $user->id);
        }

        if ($request->has('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return response()->json(['data' => $query->paginate(20)]);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $farmer   = Farmer::findOrFail($request->farmer_id);
        $operator = $request->user();

        try {
            $transaction = $this->transactionService->createTransaction(
                $farmer,
                $operator,
                $request->items,
                $request->payment_method,
                $request->notes
            );

            return response()->json([
                'message' => 'Transaction completed successfully.',
                'data'    => $transaction,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Transaction $transaction): JsonResponse
    {
        return response()->json(['data' => $transaction->load('farmer', 'operator', 'items', 'debt')]);
    }
}
