<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\ProductModel;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = InventoryStock::with(['model.subcategory.category', 'warehouse']);

        if ($user->isSuperAdmin()) {
            if ($request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
        } else {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        if ($request->search) {
            $query->whereHas('model', function ($q) use ($request) {
                $q->where('model_name', 'like', '%' . $request->search . '%');
            });
        }

        // Group inventory by Warehouse → Category → Subcategory with aggregated totals
        $groupedInventory = [];

        if ($user->isSuperAdmin()) {
            // Get all warehouses
            $warehousesList = Warehouse::where('status', 'active')->get();

            // Get all inventory stocks
            $allStocks = InventoryStock::with(['model.subcategory.category', 'warehouse'])->get();

            // Group by warehouse
            foreach ($warehousesList as $warehouse) {
                $warehouseStocks = $allStocks->where('warehouse_id', $warehouse->id);

                if ($warehouseStocks->isEmpty()) {
                    continue;
                }

                $categoryGroups = [];

                // Group by category, then by subcategory
                foreach ($warehouseStocks as $stock) {
                    if (!$stock->model || !$stock->model->subcategory || !$stock->model->subcategory->category) {
                        continue;
                    }

                    $category = $stock->model->subcategory->category;
                    $subcategory = $stock->model->subcategory;

                    $categoryId = $category->id;
                    $subcategoryId = $subcategory->id;

                    // Initialize category if not exists
                    if (!isset($categoryGroups[$categoryId])) {
                        $categoryGroups[$categoryId] = [
                            'id' => $categoryId,
                            'name' => $category->name,
                            'subcategories' => []
                        ];
                    }

                    // Initialize subcategory if not exists
                    if (!isset($categoryGroups[$categoryId]['subcategories'][$subcategoryId])) {
                        $categoryGroups[$categoryId]['subcategories'][$subcategoryId] = [
                            'id' => $subcategoryId,
                            'name' => $subcategory->name,
                            'total_stock' => 0,
                            'available_stock' => 0,
                            'models' => []
                        ];
                    }

                    // Get model info
                    $model = $stock->model;
                    $modelId = $model->id;

                    // Initialize model if not exists
                    if (!isset($categoryGroups[$categoryId]['subcategories'][$subcategoryId]['models'][$modelId])) {
                        $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['models'][$modelId] = [
                            'id' => $modelId,
                            'name' => $model->model_name,
                            'total_stock' => 0,
                            'available_stock' => 0
                        ];
                    }

                    // Aggregate stock totals for this model
                    $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['models'][$modelId]['total_stock'] += $stock->total_stock;
                    $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['models'][$modelId]['available_stock'] += $stock->available_stock;

                    // Aggregate stock totals for this subcategory
                    $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['total_stock'] += $stock->total_stock;
                    $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['available_stock'] += $stock->available_stock;
                }

                if (!empty($categoryGroups)) {
                    $groupedInventory[$warehouse->id] = [
                        'warehouse' => $warehouse,
                        'categories' => $categoryGroups
                    ];
                }
            }
        } else {
            // For Admin/Employee: Show only their warehouse
            $warehouse = $user->warehouse;
            if ($warehouse) {
                $warehouseStocks = InventoryStock::with(['model.subcategory.category', 'warehouse'])
                    ->where('warehouse_id', $warehouse->id)
                    ->get();

                if ($warehouseStocks->isNotEmpty()) {
                    $categoryGroups = [];

                    foreach ($warehouseStocks as $stock) {
                        if (!$stock->model || !$stock->model->subcategory || !$stock->model->subcategory->category) {
                            continue;
                        }

                        $category = $stock->model->subcategory->category;
                        $subcategory = $stock->model->subcategory;

                        $categoryId = $category->id;
                        $subcategoryId = $subcategory->id;

                        if (!isset($categoryGroups[$categoryId])) {
                            $categoryGroups[$categoryId] = [
                                'id' => $categoryId,
                                'name' => $category->name,
                                'subcategories' => []
                            ];
                        }

                        if (!isset($categoryGroups[$categoryId]['subcategories'][$subcategoryId])) {
                            $categoryGroups[$categoryId]['subcategories'][$subcategoryId] = [
                                'id' => $subcategoryId,
                                'name' => $subcategory->name,
                                'total_stock' => 0,
                                'available_stock' => 0,
                                'models' => []
                            ];
                        }

                        // Get model info
                        $model = $stock->model;
                        $modelId = $model->id;

                        // Initialize model if not exists
                        if (!isset($categoryGroups[$categoryId]['subcategories'][$subcategoryId]['models'][$modelId])) {
                            $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['models'][$modelId] = [
                                'id' => $modelId,
                                'name' => $model->model_name,
                                'total_stock' => 0,
                                'available_stock' => 0
                            ];
                        }

                        // Aggregate stock totals for this model
                        $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['models'][$modelId]['total_stock'] += $stock->total_stock;
                        $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['models'][$modelId]['available_stock'] += $stock->available_stock;

                        // Aggregate stock totals for this subcategory
                        $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['total_stock'] += $stock->total_stock;
                        $categoryGroups[$categoryId]['subcategories'][$subcategoryId]['available_stock'] += $stock->available_stock;
                    }

                    if (!empty($categoryGroups)) {
                        $groupedInventory[$warehouse->id] = [
                            'warehouse' => $warehouse,
                            'categories' => $categoryGroups
                        ];
                    }
                }
            }
        }

        // For transfer modal: Super Admin sees all warehouses, Admin/Employee see all warehouses for transfers
        $warehouses = Warehouse::where('status', 'active')->get();
        $categories = InventoryCategory::with(['subcategories' => function ($q) {
            $q->with('models');
        }])->get();

        return view('inventory.index', compact('groupedInventory', 'warehouses', 'categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'model_id' => 'required|exists:models,id',
            'warehouse_id' => $user->isSuperAdmin() ? 'required|exists:warehouses,id' : 'nullable',
            'qty' => 'required|integer|min:1',
            'invoice' => 'nullable|file|mimes:jpg,jpeg,pdf|max:51200',
            'remarks' => 'nullable|string',
        ]);

        if (!$user->isSuperAdmin()) {
            $data['warehouse_id'] = $user->warehouse_id;
        }

        $invoicePath = null;
        if ($request->hasFile('invoice')) {
            $invoicePath = $request->file('invoice')->store('invoices', 'public');
        }

        $stock = InventoryStock::firstOrCreate(
            ['model_id' => $data['model_id'], 'warehouse_id' => $data['warehouse_id']],
            ['total_stock' => 0, 'available_stock' => 0, 'created_by' => $user->id]
        );

        $stock->increment('total_stock', $data['qty']);
        $stock->increment('available_stock', $data['qty']);

        InventoryTransaction::create([
            'model_id' => $data['model_id'],
            'warehouse_id' => $data['warehouse_id'],
            'qty' => $data['qty'],
            'type' => 'add',
            'invoice_path' => $invoicePath,
            'created_by' => $user->id,
            'remarks' => $data['remarks'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventory added successfully'
        ]);
    }

    public function deduct(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'model_id' => 'required|exists:models,id',
            'warehouse_id' => $user->isSuperAdmin() ? 'required|exists:warehouses,id' : 'nullable',
            'qty' => 'required|integer|min:1',
            'invoice' => 'required|file|mimes:jpg,jpeg,pdf|max:51200',
            'remarks' => 'nullable|string',
        ]);

        if (!$user->isSuperAdmin()) {
            $data['warehouse_id'] = $user->warehouse_id;
        }

        $stock = InventoryStock::where('model_id', $data['model_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->firstOrFail();

        if ($stock->available_stock < $data['qty']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }

        $invoicePath = null;
        if ($request->hasFile('invoice')) {
            $invoicePath = $request->file('invoice')->store('invoices', 'public');
        }

        $stock->decrement('total_stock', $data['qty']);
        $stock->decrement('available_stock', $data['qty']);

        // Refresh the stock model to get updated values
        $stock->refresh();

        InventoryTransaction::create([
            'model_id' => $data['model_id'],
            'warehouse_id' => $data['warehouse_id'],
            'qty' => $data['qty'],
            'type' => 'deduct',
            'invoice_path' => $invoicePath,
            'created_by' => $user->id,
            'remarks' => $data['remarks'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventory deducted successfully'
        ]);
    }

    public function transfer(Request $request)
    {
        $user = auth()->user();
        
        $data = $request->validate([
            'model_id' => 'required|exists:models,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'qty' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        // For Admin/Employee, ensure they can only transfer FROM their warehouse
        if (!$user->isSuperAdmin()) {
            if ($data['from_warehouse_id'] != $user->warehouse_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only transfer stock from your assigned warehouse'
                ], 403);
            }
        }

        $fromStock = InventoryStock::where('model_id', $data['model_id'])
            ->where('warehouse_id', $data['from_warehouse_id'])
            ->firstOrFail();

        if ($fromStock->available_stock < $data['qty']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock in source warehouse'
            ], 400);
        }

        $toStock = InventoryStock::firstOrCreate(
            ['model_id' => $data['model_id'], 'warehouse_id' => $data['to_warehouse_id']],
            ['total_stock' => 0, 'available_stock' => 0, 'created_by' => auth()->id()]
        );

        $fromStock->decrement('total_stock', $data['qty']);
        $fromStock->decrement('available_stock', $data['qty']);
        $toStock->increment('total_stock', $data['qty']);
        $toStock->increment('available_stock', $data['qty']);

        InventoryTransaction::create([
            'model_id' => $data['model_id'],
            'warehouse_id' => $data['from_warehouse_id'],
            'qty' => $data['qty'],
            'type' => 'transfer',
            'created_by' => auth()->id(),
            'remarks' => $data['remarks'] ?? null,
            'transfer_from_warehouse_id' => $data['from_warehouse_id'],
            'transfer_to_warehouse_id' => $data['to_warehouse_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock transferred successfully'
        ]);
    }

    public function getSubcategories($categoryId)
    {
        $subcategories = \App\Models\InventorySubcategory::where('category_id', $categoryId)->get();
        return response()->json([
            'subcategories' => $subcategories
        ]);
    }

    public function getModels($subcategoryId)
    {
        $models = ProductModel::where('subcategory_id', $subcategoryId)->get();
        return response()->json([
            'models' => $models
        ]);
    }

    public function getAvailableStock(Request $request)
    {
        $user = auth()->user();
        $modelId = $request->model_id;
        $warehouseId = $user->isSuperAdmin() ? $request->warehouse_id : $user->warehouse_id;

        if (!$modelId || !$warehouseId) {
            return response()->json([
                'available_stock' => 0
            ]);
        }

        $stock = InventoryStock::where('model_id', $modelId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return response()->json([
            'available_stock' => $stock ? $stock->available_stock : 0,
            'total_stock' => $stock ? $stock->total_stock : 0
        ]);
    }
}
