<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventorySubcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = InventorySubcategory::with(['category', 'models'])->latest()->get();
        $categories = InventoryCategory::all();
        return view('masters.subcategories.index', compact('subcategories', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255|unique:inventory_subcategories,name,NULL,id,category_id,' . $request->category_id
        ]);

        InventorySubcategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory created successfully'
        ]);
    }

    public function show($id)
    {
        $subcategory = InventorySubcategory::with('category')->findOrFail($id);
        return response()->json($subcategory);
    }

    public function update(Request $request, $id)
    {
        $subcategory = InventorySubcategory::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255|unique:inventory_subcategories,name,' . $id . ',id,category_id,' . $request->category_id
        ]);

        $subcategory->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $subcategory = InventorySubcategory::findOrFail($id);

        // Check if subcategory has models
        if ($subcategory->models()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subcategory with existing models'
            ], 400);
        }

        $subcategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategory deleted successfully'
        ]);
    }
}
