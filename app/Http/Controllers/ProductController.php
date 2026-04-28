<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        return response()->json(['message' => 'Product created.', 'data' => $product->load('category')], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json(['data' => $product->load('category')]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());
        return response()->json(['message' => 'Product updated.', 'data' => $product->load('category')]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->update(['is_active' => false]);
        return response()->json(['message' => 'Product deactivated.']);
    }
}
