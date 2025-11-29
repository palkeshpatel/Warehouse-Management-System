<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = InventoryCategory::with('subcategories')->latest()->get();
        return view('masters.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:inventory_categories,name'
        ]);

        InventoryCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully'
        ]);
    }

    public function show($id)
    {
        $category = InventoryCategory::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = InventoryCategory::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:inventory_categories,name,' . $id
        ]);

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $category = InventoryCategory::findOrFail($id);

        // Check if category has subcategories
        if ($category->subcategories()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing subcategories'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
