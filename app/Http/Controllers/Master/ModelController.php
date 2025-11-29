<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventorySubcategory;
use App\Models\ProductModel;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    public function index()
    {
        $models = ProductModel::with(['subcategory.category'])->latest()->get();
        $categories = InventoryCategory::with('subcategories')->get();
        return view('masters.models.index', compact('models', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subcategory_id' => 'required|exists:inventory_subcategories,id',
            'model_name' => 'required|string|max:255|unique:models,model_name,NULL,id,subcategory_id,' . $request->subcategory_id
        ]);

        ProductModel::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Model created successfully'
        ]);
    }

    public function show($id)
    {
        $model = ProductModel::with(['subcategory.category'])->findOrFail($id);
        return response()->json($model);
    }

    public function update(Request $request, $id)
    {
        $model = ProductModel::findOrFail($id);

        $data = $request->validate([
            'subcategory_id' => 'required|exists:inventory_subcategories,id',
            'model_name' => 'required|string|max:255|unique:models,model_name,' . $id . ',id,subcategory_id,' . $request->subcategory_id
        ]);

        $model->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Model updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $model = ProductModel::findOrFail($id);

        // Check if model has inventory stock
        if ($model->inventoryStock()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete model with existing inventory stock'
            ], 400);
        }

        $model->delete();

        return response()->json([
            'success' => true,
            'message' => 'Model deleted successfully'
        ]);
    }
}
