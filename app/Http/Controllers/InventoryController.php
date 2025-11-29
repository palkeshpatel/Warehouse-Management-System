<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\ProductModel;
use App\Models\Warehouse;
use Illuminate\Http\Request;
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

        $inventory = $query->orderBy('id', 'desc')->paginate(15);
        $warehouses = $user->isSuperAdmin() ? Warehouse::where('status', 'active')->get() : collect();
        $categories = InventoryCategory::with(['subcategories' => function ($q) {
            $q->with('models');
        }])->get();

        return view('inventory.index', compact('inventory', 'warehouses', 'categories'));
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
        $data = $request->validate([
            'model_id' => 'required|exists:models,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'qty' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

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
}
