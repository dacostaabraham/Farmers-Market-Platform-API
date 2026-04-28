<?php

namespace App\Http\Controllers;

use App\Http\Requests\Farmer\StoreFarmerRequest;
use App\Http\Requests\Farmer\UpdateFarmerRequest;
use App\Models\Farmer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmerController extends Controller
{
    public function index(): JsonResponse
    {
        $farmers = Farmer::where('is_active', true)->get();
        return response()->json(['data' => $farmers]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $q = $request->q;
        $farmers = Farmer::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('identifier', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('firstname', 'like', "%{$q}%")
                      ->orWhere('lastname', 'like', "%{$q}%");
            })
            ->get();

        return response()->json(['data' => $farmers]);
    }

    public function store(StoreFarmerRequest $request): JsonResponse
    {
        $farmer = Farmer::create($request->validated());
        return response()->json(['message' => 'Farmer created.', 'data' => $farmer], 201);
    }

    public function show(Farmer $farmer): JsonResponse
    {
        $farmer->load(['debts' => function ($q) {
            $q->whereIn('status', ['open', 'partially_paid'])->orderBy('created_at');
        }]);

        return response()->json([
            'data' => array_merge($farmer->toArray(), [
                'total_outstanding_debt' => $farmer->total_outstanding_debt,
                'available_credit'       => $farmer->available_credit,
            ]),
        ]);
    }

    public function update(UpdateFarmerRequest $request, Farmer $farmer): JsonResponse
    {
        $farmer->update($request->validated());
        return response()->json(['message' => 'Farmer updated.', 'data' => $farmer]);
    }

    public function destroy(Farmer $farmer): JsonResponse
    {
        $farmer->update(['is_active' => false]);
        return response()->json(['message' => 'Farmer deactivated.']);
    }
}
