<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with('parent')->where('is_active', true)->get();
        return response()->json(['data' => $categories]);
    }

    /** Full nested tree (for navigation in Flutter app) */
    public function tree(): JsonResponse
    {
        $tree = Category::with('allChildren')
                        ->whereNull('parent_id')
                        ->where('is_active', true)
                        ->get();
        return response()->json(['data' => $tree]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $level = 1;
        if ($request->parent_id) {
            $parent = Category::findOrFail($request->parent_id);
            $level  = $parent->level + 1;
        }

        $category = Category::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name) . '-' . uniqid(),
            'description' => $request->description,
            'parent_id'   => $request->parent_id,
            'level'       => $level,
        ]);

        return response()->json(['message' => 'Category created.', 'data' => $category], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json(['data' => $category->load('parent', 'children')]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());
        return response()->json(['message' => 'Category updated.', 'data' => $category]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->children()->exists()) {
            return response()->json(['message' => 'Cannot delete a category with subcategories.'], 422);
        }
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Cannot delete a category with products.'], 422);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted.']);
    }

    /** Products belonging to a category (paginated) */
    public function products(Category $category): JsonResponse
    {
        $products = $category->products()->where('is_active', true)->get();
        return response()->json(['data' => $products]);
    }
}
