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
            'transaction_subtype' => 'required|in:purchase_stock,sales_return',
            'sales_return_reason_id' => 'nullable|required_if:transaction_subtype,sales_return|exists:sales_return_reasons,id',
            'reason_other' => 'nullable|string',
            'invoice' => 'nullable|file|mimes:jpg,jpeg,pdf|max:51200',
            'invoice_no' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        // Validate reason_other if "Other" reason is selected
        if ($data['transaction_subtype'] === 'sales_return' && isset($data['sales_return_reason_id'])) {
            $reason = \App\Models\SalesReturnReason::find($data['sales_return_reason_id']);
            if ($reason && strtolower($reason->name) === 'other' && empty($data['reason_other'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please specify the reason for "Other" option'
                ], 422);
            }
        }

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
            'transaction_subtype' => $data['transaction_subtype'],
            'sales_return_reason_id' => $data['transaction_subtype'] === 'sales_return' ? ($data['sales_return_reason_id'] ?? null) : null,
            'reason_other' => $data['reason_other'] ?? null,
            'invoice_path' => $invoicePath,
            'invoice_no' => $data['invoice_no'] ?? null,
            'invoice_date' => $data['invoice_date'] ?? null,
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
            'transaction_subtype' => 'required|in:sales,purchase_return',
            'purchase_return_reason_id' => 'nullable|required_if:transaction_subtype,purchase_return|exists:purchase_return_reasons,id',
            'reason_other' => 'nullable|string',
            'invoice' => 'nullable|file|mimes:jpg,jpeg,pdf|max:51200',
            'invoice_no' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        // Validate reason_other if "Other" reason is selected
        if ($data['transaction_subtype'] === 'purchase_return' && isset($data['purchase_return_reason_id'])) {
            $reason = \App\Models\PurchaseReturnReason::find($data['purchase_return_reason_id']);
            if ($reason && strtolower($reason->name) === 'other' && empty($data['reason_other'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please specify the reason for "Other" option'
                ], 422);
            }
        }

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
            'transaction_subtype' => $data['transaction_subtype'],
            'purchase_return_reason_id' => $data['transaction_subtype'] === 'purchase_return' ? ($data['purchase_return_reason_id'] ?? null) : null,
            'reason_other' => $data['reason_other'] ?? null,
            'invoice_path' => $invoicePath,
            'invoice_no' => $data['invoice_no'] ?? null,
            'invoice_date' => $data['invoice_date'] ?? null,
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

    public function editTransaction($id)
    {
        $user = auth()->user();
        if ($user->isEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $transaction = InventoryTransaction::with(['model.subcategory.category', 'warehouse'])->findOrFail($id);

        if ($user->isAdmin() && $transaction->warehouse_id !== $user->warehouse_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. You can only edit transactions in your assigned warehouse.'
            ], 403);
        }

        // Fetch subcategories for the category
        $categoryId = $transaction->model->subcategory->category_id;
        $subcategories = \App\Models\InventorySubcategory::where('category_id', $categoryId)->get();

        // Fetch models for the subcategory
        $subcategoryId = $transaction->model->subcategory_id;
        $models = ProductModel::where('subcategory_id', $subcategoryId)->get();

        // Fetch return reasons
        $salesReturnReasons = \App\Models\SalesReturnReason::where('is_active', true)->get();
        $purchaseReturnReasons = \App\Models\PurchaseReturnReason::where('is_active', true)->get();

        // Fetch available stock (pre-edit) to help show limits
        $stock = InventoryStock::where('model_id', $transaction->model_id)
            ->where('warehouse_id', $transaction->warehouse_id)
            ->first();

        return response()->json([
            'success' => true,
            'transaction' => $transaction,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'subcategories' => $subcategories,
            'models' => $models,
            'sales_return_reasons' => $salesReturnReasons,
            'purchase_return_reasons' => $purchaseReturnReasons,
            'available_stock' => $stock ? $stock->available_stock : 0,
            'total_stock' => $stock ? $stock->total_stock : 0,
        ]);
    }

    public function updateTransaction(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->isEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $transaction = InventoryTransaction::findOrFail($id);

        if ($user->isAdmin() && $transaction->warehouse_id !== $user->warehouse_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // Validate basic fields
        $rules = [
            'model_id' => 'required|exists:models,id',
            'qty' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ];

        // Type specific rules
        if ($transaction->type === 'add') {
            $rules['warehouse_id'] = $user->isSuperAdmin() ? 'required|exists:warehouses,id' : 'nullable';
            $rules['transaction_subtype'] = 'required|in:purchase_stock,sales_return';
            $rules['sales_return_reason_id'] = 'nullable|required_if:transaction_subtype,sales_return|exists:sales_return_reasons,id';
            $rules['reason_other'] = 'nullable|string';
            $rules['invoice'] = 'nullable|file|mimes:jpg,jpeg,pdf|max:51200';
            $rules['invoice_no'] = 'nullable|string|max:255';
            $rules['invoice_date'] = 'nullable|date';
        } elseif ($transaction->type === 'deduct') {
            $rules['warehouse_id'] = $user->isSuperAdmin() ? 'required|exists:warehouses,id' : 'nullable';
            $rules['transaction_subtype'] = 'required|in:sales,purchase_return';
            $rules['purchase_return_reason_id'] = 'nullable|required_if:transaction_subtype,purchase_return|exists:purchase_return_reasons,id';
            $rules['reason_other'] = 'nullable|string';
            $rules['invoice'] = 'nullable|file|mimes:jpg,jpeg,pdf|max:51200';
            $rules['invoice_no'] = 'nullable|string|max:255';
            $rules['invoice_date'] = 'nullable|date';
        } elseif ($transaction->type === 'transfer') {
            $rules['from_warehouse_id'] = 'required|exists:warehouses,id';
            $rules['to_warehouse_id'] = 'required|exists:warehouses,id|different:from_warehouse_id';
        }

        $data = $request->validate($rules);

        // Subtype check and specify other reason check
        if ($transaction->type === 'add' && $data['transaction_subtype'] === 'sales_return' && isset($data['sales_return_reason_id'])) {
            $reason = \App\Models\SalesReturnReason::find($data['sales_return_reason_id']);
            if ($reason && strtolower($reason->name) === 'other' && empty($data['reason_other'])) {
                return response()->json(['success' => false, 'message' => 'Please specify the reason for "Other" option'], 422);
            }
        }
        if ($transaction->type === 'deduct' && $data['transaction_subtype'] === 'purchase_return' && isset($data['purchase_return_reason_id'])) {
            $reason = \App\Models\PurchaseReturnReason::find($data['purchase_return_reason_id']);
            if ($reason && strtolower($reason->name) === 'other' && empty($data['reason_other'])) {
                return response()->json(['success' => false, 'message' => 'Please specify the reason for "Other" option'], 422);
            }
        }

        // Set warehouse_id if not super admin
        if ($transaction->type !== 'transfer' && !$user->isSuperAdmin()) {
            $data['warehouse_id'] = $user->warehouse_id;
        }

        $newModelId = $data['model_id'];
        $newWarehouseId = $transaction->type === 'transfer' ? null : ($data['warehouse_id'] ?? $transaction->warehouse_id);
        $newQty = (int)$data['qty'];

        $newTransferFrom = $transaction->type === 'transfer' ? $data['from_warehouse_id'] : null;
        $newTransferTo = $transaction->type === 'transfer' ? $data['to_warehouse_id'] : null;

        // Perform stock updates and validation in a transaction
        try {
            DB::beginTransaction();

            // 1. REVERSE the old transaction's effect on stock
            if ($transaction->type === 'add') {
                $oldStock = InventoryStock::where('model_id', $transaction->model_id)
                    ->where('warehouse_id', $transaction->warehouse_id)
                    ->first();
                if ($oldStock) {
                    $oldStock->decrement('total_stock', $transaction->qty);
                    $oldStock->decrement('available_stock', $transaction->qty);
                }
            } elseif ($transaction->type === 'deduct') {
                $oldStock = InventoryStock::where('model_id', $transaction->model_id)
                    ->where('warehouse_id', $transaction->warehouse_id)
                    ->first();
                if ($oldStock) {
                    $oldStock->increment('total_stock', $transaction->qty);
                    $oldStock->increment('available_stock', $transaction->qty);
                }
            } elseif ($transaction->type === 'transfer') {
                // Reverse from warehouse: increment source
                $fromStock = InventoryStock::where('model_id', $transaction->model_id)
                    ->where('warehouse_id', $transaction->transfer_from_warehouse_id)
                    ->first();
                if ($fromStock) {
                    $fromStock->increment('total_stock', $transaction->qty);
                    $fromStock->increment('available_stock', $transaction->qty);
                }

                // Reverse to warehouse: decrement target
                $toStock = InventoryStock::where('model_id', $transaction->model_id)
                    ->where('warehouse_id', $transaction->transfer_to_warehouse_id)
                    ->first();
                if ($toStock) {
                    $toStock->decrement('total_stock', $transaction->qty);
                    $toStock->decrement('available_stock', $transaction->qty);
                }
            }

            // 2. APPLY the new transaction's effect on stock
            if ($transaction->type === 'add') {
                $newStock = InventoryStock::firstOrCreate(
                    ['model_id' => $newModelId, 'warehouse_id' => $newWarehouseId],
                    ['total_stock' => 0, 'available_stock' => 0, 'created_by' => $user->id]
                );
                $newStock->increment('total_stock', $newQty);
                $newStock->increment('available_stock', $newQty);
            } elseif ($transaction->type === 'deduct') {
                $newStock = InventoryStock::firstOrCreate(
                    ['model_id' => $newModelId, 'warehouse_id' => $newWarehouseId],
                    ['total_stock' => 0, 'available_stock' => 0, 'created_by' => $user->id]
                );
                $newStock->decrement('total_stock', $newQty);
                $newStock->decrement('available_stock', $newQty);
            } elseif ($transaction->type === 'transfer') {
                // Source warehouse
                $fromStock = InventoryStock::firstOrCreate(
                    ['model_id' => $newModelId, 'warehouse_id' => $newTransferFrom],
                    ['total_stock' => 0, 'available_stock' => 0, 'created_by' => $user->id]
                );
                $fromStock->decrement('total_stock', $newQty);
                $fromStock->decrement('available_stock', $newQty);

                // Target warehouse
                $toStock = InventoryStock::firstOrCreate(
                    ['model_id' => $newModelId, 'warehouse_id' => $newTransferTo],
                    ['total_stock' => 0, 'available_stock' => 0, 'created_by' => $user->id]
                );
                $toStock->increment('total_stock', $newQty);
                $toStock->increment('available_stock', $newQty);
            }

            // 3. VALIDATION - Ensure no stock drops below zero
            // Collect all stock IDs we changed to check their values
            $changedStocks = [];
            if ($transaction->type === 'transfer') {
                $changedStocks[] = InventoryStock::where('model_id', $transaction->model_id)->where('warehouse_id', $transaction->transfer_from_warehouse_id)->first();
                $changedStocks[] = InventoryStock::where('model_id', $transaction->model_id)->where('warehouse_id', $transaction->transfer_to_warehouse_id)->first();
                $changedStocks[] = InventoryStock::where('model_id', $newModelId)->where('warehouse_id', $newTransferFrom)->first();
                $changedStocks[] = InventoryStock::where('model_id', $newModelId)->where('warehouse_id', $newTransferTo)->first();
            } else {
                $changedStocks[] = InventoryStock::where('model_id', $transaction->model_id)->where('warehouse_id', $transaction->warehouse_id)->first();
                $changedStocks[] = InventoryStock::where('model_id', $newModelId)->where('warehouse_id', $newWarehouseId)->first();
            }

            foreach ($changedStocks as $stock) {
                if ($stock && ($stock->available_stock < 0 || $stock->total_stock < 0)) {
                    DB::rollBack();
                    $modelName = $stock->model->model_name ?? 'Unknown';
                    $warehouseName = $stock->warehouse->name ?? 'Unknown';
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock. This change would result in a negative stock level (available: {$stock->available_stock}) for Model '{$modelName}' in '{$warehouseName}'."
                    ], 400);
                }
            }

            // 4. Handle invoice file upload
            $invoicePath = $transaction->invoice_path;
            if ($request->hasFile('invoice')) {
                // Delete old invoice file if it exists
                if ($transaction->invoice_path) {
                    Storage::disk('public')->delete($transaction->invoice_path);
                }
                $invoicePath = $request->file('invoice')->store('invoices', 'public');
            }

            // 5. LOG the edit
            \App\Models\InventoryLog::create([
                'inventory_transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'action' => 'update',
                'old_qty' => $transaction->qty,
                'new_qty' => $newQty,
                'old_model_id' => $transaction->model_id,
                'new_model_id' => $newModelId,
                'old_warehouse_id' => $transaction->type === 'transfer' ? null : $transaction->warehouse_id,
                'new_warehouse_id' => $transaction->type === 'transfer' ? null : $newWarehouseId,
                'old_transaction_subtype' => $transaction->transaction_subtype,
                'new_transaction_subtype' => $transaction->type === 'transfer' ? null : ($data['transaction_subtype'] ?? null),
                'old_invoice_no' => $transaction->invoice_no,
                'new_invoice_no' => $transaction->type === 'transfer' ? null : ($data['invoice_no'] ?? null),
                'old_invoice_date' => $transaction->invoice_date,
                'new_invoice_date' => $transaction->type === 'transfer' ? null : ($data['invoice_date'] ?? null),
                'old_invoice_path' => $transaction->invoice_path,
                'new_invoice_path' => $invoicePath,
                'old_remarks' => $transaction->remarks,
                'new_remarks' => $data['remarks'] ?? null,
            ]);

            // 6. Update the transaction record
            $updateData = [
                'model_id' => $newModelId,
                'qty' => $newQty,
                'remarks' => $data['remarks'] ?? null,
            ];

            if ($transaction->type === 'transfer') {
                $updateData['transfer_from_warehouse_id'] = $newTransferFrom;
                $updateData['transfer_to_warehouse_id'] = $newTransferTo;
                $updateData['warehouse_id'] = $newTransferFrom; // keep warehouse_id as source for transfer
            } else {
                $updateData['warehouse_id'] = $newWarehouseId;
                $updateData['transaction_subtype'] = $data['transaction_subtype'];
                $updateData['invoice_path'] = $invoicePath;
                $updateData['invoice_no'] = $data['invoice_no'] ?? null;
                $updateData['invoice_date'] = $data['invoice_date'] ?? null;
                
                if ($transaction->type === 'add') {
                    $updateData['sales_return_reason_id'] = $data['transaction_subtype'] === 'sales_return' ? ($data['sales_return_reason_id'] ?? null) : null;
                    $updateData['reason_other'] = $data['transaction_subtype'] === 'sales_return' ? ($data['reason_other'] ?? null) : null;
                } elseif ($transaction->type === 'deduct') {
                    $updateData['purchase_return_reason_id'] = $data['transaction_subtype'] === 'purchase_return' ? ($data['purchase_return_reason_id'] ?? null) : null;
                    $updateData['reason_other'] = $data['transaction_subtype'] === 'purchase_return' ? ($data['reason_other'] ?? null) : null;
                }
            }

            $transaction->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory transaction updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the transaction: ' . $e->getMessage()
            ], 500);
        }
    }
}
