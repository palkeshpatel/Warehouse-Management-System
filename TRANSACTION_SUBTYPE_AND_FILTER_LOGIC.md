# Transaction Subtype & Filter Logic Documentation

## 1. TRANSACTION SUBTYPE - Database & Model

### Database Schema (Migration)

**File:** `database/migrations/2026_01_28_140721_add_transaction_subtype_to_inventory_transactions_table.php`

```php
$table->enum('transaction_subtype', ['purchase_stock', 'sales_return', 'sales', 'purchase_return'])
    ->nullable()
    ->after('type');
```

**Four Subtypes:**

- `purchase_stock` - New stock being purchased
- `sales_return` - Customer returns the product
- `sales` - Product sold to customer
- `purchase_return` - Returning stock to supplier

### Model

**File:** `app/Models/InventoryTransaction.php`

```php
protected $fillable = [
    ...
    'transaction_subtype',
    'sales_return_reason_id',
    'purchase_return_reason_id',
    ...
];
```

---

## 2. TRANSACTION SUBTYPE - Add Inventory Flow

**File:** `app/Http/Controllers/InventoryController.php` (Lines 197-250)

### Store (ADD) - Validation & Logic

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'transaction_subtype' => 'required|in:purchase_stock,sales_return',
        'sales_return_reason_id' => 'nullable|required_if:transaction_subtype,sales_return|exists:sales_return_reasons,id',
        ...
    ]);

    // Set transaction subtype
    if ($data['transaction_subtype'] === 'sales_return' && isset($data['sales_return_reason_id'])) {
        $data['reason_other'] = null;
    }

    // Create transaction
    InventoryTransaction::create([
        'transaction_subtype' => $data['transaction_subtype'],
        'sales_return_reason_id' => $data['transaction_subtype'] === 'sales_return'
            ? ($data['sales_return_reason_id'] ?? null)
            : null,
        ...
    ]);
}
```

**For ADD operation:**

- Can only be: `purchase_stock` or `sales_return`
- If `sales_return`: Must have a return reason selected

### View/Form

**File:** `resources/views/inventory/modals/add.blade.php` (Line 39)

```html
<input
    type="hidden"
    name="transaction_subtype"
    id="addTransactionSubtype"
    required
/>

<!-- Sales Return Reason Section (conditionally shown) -->
<div class="row mb-3" id="salesReturnReasonSection" style="display: none;">
    <div class="col-12">
        <label class="form-label"
            >Return Reason <span class="text-danger">*</span></label
        >
        <select
            name="sales_return_reason_id"
            class="form-select"
            id="addSalesReturnReason"
        >
            <option value="">Select Reason</option>
            @foreach(\App\Models\SalesReturnReason::where('is_active',
            true)->get() as $reason)
            <option value="{{ $reason->id }}">{{ $reason->name }}</option>
            @endforeach
        </select>
    </div>
</div>
```

---

## 3. TRANSACTION SUBTYPE - Deduct Inventory Flow

**File:** `app/Http/Controllers/InventoryController.php` (Lines 259-320)

### Deduct - Validation & Logic

```php
public function deduct(Request $request)
{
    $validated = $request->validate([
        'transaction_subtype' => 'required|in:sales,purchase_return',
        'purchase_return_reason_id' => 'nullable|required_if:transaction_subtype,purchase_return|exists:purchase_return_reasons,id',
        ...
    ]);

    // Set transaction subtype
    if ($data['transaction_subtype'] === 'purchase_return' && isset($data['purchase_return_reason_id'])) {
        $data['reason_other'] = null;
    }

    // Create transaction
    InventoryTransaction::create([
        'transaction_subtype' => $data['transaction_subtype'],
        'purchase_return_reason_id' => $data['transaction_subtype'] === 'purchase_return'
            ? ($data['purchase_return_reason_id'] ?? null)
            : null,
        ...
    ]);
}
```

**For DEDUCT operation:**

- Can only be: `sales` or `purchase_return`
- If `purchase_return`: Must have a return reason selected

### View/Form

**File:** `resources/views/inventory/modals/deduct.blade.php` (Line 39)

```html
<input
    type="hidden"
    name="transaction_subtype"
    id="deductTransactionSubtype"
    required
/>

<!-- Purchase Return Reason Section (conditionally shown) -->
<div class="row mb-3" id="purchaseReturnReasonSection" style="display: none;">
    <div class="col-12">
        <label class="form-label"
            >Return Reason <span class="text-danger">*</span></label
        >
        <select
            name="purchase_return_reason_id"
            class="form-select"
            id="deductPurchaseReturnReason"
        >
            <option value="">Select Reason</option>
            @foreach(\App\Models\PurchaseReturnReason::where('is_active',
            true)->get() as $reason)
            <option value="{{ $reason->id }}">{{ $reason->name }}</option>
            @endforeach
        </select>
    </div>
</div>
```

---

## 4. TRANSACTION SUBTYPE - Transaction Type Relationship

| Operation      | Type       | Subtypes                         |
| -------------- | ---------- | -------------------------------- |
| Add Stock      | `add`      | `purchase_stock`, `sales_return` |
| Deduct Stock   | `deduct`   | `sales`, `purchase_return`       |
| Transfer Stock | `transfer` | (no subtype)                     |

**Logic Flow:**

```
ADD (Type) → Purchase Stock (Subtype) = New inventory purchased
ADD (Type) → Sales Return (Subtype) = Customer returned the product

DEDUCT (Type) → Sales (Subtype) = Sold to customer
DEDUCT (Type) → Purchase Return (Subtype) = Returning to supplier

TRANSFER (Type) → No subtype (special handling)
```

---

## 5. FILTER LOGIC - Reports

**File:** `app/Http/Controllers/ReportController.php`

### Current Filter Implementation

```php
public function filterReports(Request $request)
{
    $user = auth()->user();
    $warehouseId = $user->isSuperAdmin()
        ? ($request->warehouse_id ?: null)
        : $user->warehouse_id;
    $period = $request->period ?? 'monthly';
    $startDate = $request->start_date;
    $endDate = $request->end_date;

    // Validate custom dates
    if ($period === 'custom' && (!$startDate || !$endDate)) {
        return response()->json([
            'success' => false,
            'message' => 'Please select both start date and end date'
        ], 422);
    }

    $data = $this->getReportData($warehouseId, $period, $user->isSuperAdmin(),
                                  $startDate, $endDate, $request->page ?? 1);

    return response()->json([
        'success' => true,
        'data' => $data,
        'html' => view('reports.partials.transactions_table', [
            'transactions' => $data['transactions'],
            'filters' => $request->except('page')
        ])->render(),
    ]);
}
```

### Report Data Query

```php
private function getReportData($warehouseId, $period, $isSuperAdmin,
                                $startDate = null, $endDate = null, $page = 1)
{
    $query = InventoryTransaction::with(['model', 'warehouse', 'creator']);

    // Filter by warehouse
    if (!$isSuperAdmin || $warehouseId) {
        $query->where('warehouse_id', $warehouseId);
    }

    // Get date range
    if ($period === 'custom' && $startDate && $endDate) {
        $dateRange = [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ];
    } else {
        $dateRange = $this->getDateRange($period);
    }

    $query->whereBetween('created_at', $dateRange);

    // Clone query for stats calculation
    $statsQuery = clone $query;

    // Get paginated transactions
    $transactions = $query->latest('created_at')->paginate(10);

    // Calculate stats
    $totalAdded = $statsQuery->where('type', 'add')->sum('qty');
    $totalDeducted = $statsQuery->where('type', 'deduct')->sum('qty');
    $totalTransferred = $statsQuery->where('type', 'transfer')->sum('qty');

    return [
        'transactions' => $transactions,
        'totalAdded' => $totalAdded,
        'totalDeducted' => $totalDeducted,
        'totalTransferred' => $totalTransferred,
        'period' => $period,
        'dateRange' => $dateRange,
    ];
}
```

### Filter Options

**File:** `resources/views/reports/index.blade.php`

```blade
<!-- Warehouse Filter (Super Admin Only) -->
@if (auth()->user()->isSuperAdmin())
    <select name="warehouse_id" class="form-select" id="warehouseFilter">
        <option value="">All Warehouses</option>
        @foreach ($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
        @endforeach
    </select>
@endif

<!-- Period Filter -->
<select name="period" class="form-select" id="periodFilter">
    <option value="daily">Daily</option>
    <option value="weekly">Weekly</option>
    <option value="monthly">Monthly</option>
    <option value="quarterly">Quarterly</option>
    <option value="yearly">Yearly</option>
    <option value="custom">Custom Date Range</option>
</select>

<!-- Custom Date Range (shown when period = custom) -->
<div id="startDateContainer" style="display: none;">
    <input type="text" name="start_date" class="form-control" id="startDate" readonly>
</div>
<div id="endDateContainer" style="display: none;">
    <input type="text" name="end_date" class="form-control" id="endDate" readonly>
</div>
```

---

## 6. CURRENT FILTERS AVAILABLE

### In Reports Page:

✅ **Warehouse Filter** - All/Single warehouse (Super Admin only)
✅ **Period Filter** - Daily, Weekly, Monthly, Quarterly, Yearly, Custom
✅ **Date Range Filter** - Custom start & end dates
✅ **Pagination** - 10 items per page

### NOT YET IMPLEMENTED:

❌ **Transaction Type Filter** - Filter by type (add/deduct/transfer)
❌ **Transaction Subtype Filter** - Filter by subtype (purchase_stock, sales_return, sales, purchase_return)
❌ **Model Filter** - Filter by product model
❌ **User Filter** - Filter by who created the transaction
❌ **Remarks Filter** - Search by transaction remarks

---

## 7. SUGGESTED ENHANCEMENT: Add Transaction Subtype Filter

To add the transaction subtype filter shown in red box in the screenshot, you would need to:

### Step 1: Update ReportController

```php
public function filterReports(Request $request)
{
    // ... existing code ...
    $transactionSubtype = $request->transaction_subtype; // NEW

    $data = $this->getReportData($warehouseId, $period, $user->isSuperAdmin(),
                                  $startDate, $endDate, $request->page ?? 1,
                                  $transactionSubtype); // ADDED PARAM
}

private function getReportData($warehouseId, $period, $isSuperAdmin,
                                $startDate = null, $endDate = null, $page = 1,
                                $transactionSubtype = null) // NEW PARAM
{
    $query = InventoryTransaction::with(['model', 'warehouse', 'creator']);

    // ... existing warehouse & date filters ...

    // NEW: Filter by transaction subtype
    if ($transactionSubtype) {
        $query->where('transaction_subtype', $transactionSubtype);
    }

    // ... rest of query ...
}
```

### Step 2: Update Reports View

```blade
<!-- New Transaction Subtype Filter -->
<div class="col-md-2">
    <label class="form-label">Transaction Subtype</label>
    <select name="transaction_subtype" class="form-select" id="transactionSubtypeFilter">
        <option value="">All Subtypes</option>
        <option value="purchase_stock">Purchase Stock</option>
        <option value="sales_return">Sales Return</option>
        <option value="sales">Sales</option>
        <option value="purchase_return">Purchase Return</option>
    </select>
</div>
```

---

## 8. KEY ROUTES

| Route               | Method | Controller                     | Purpose             |
| ------------------- | ------ | ------------------------------ | ------------------- |
| `/reports`          | GET    | ReportController@index         | Load reports page   |
| `/reports/filter`   | POST   | ReportController@filterReports | AJAX filter request |
| `/inventory`        | POST   | InventoryController@store      | Add inventory       |
| `/inventory/deduct` | POST   | InventoryController@deduct     | Deduct inventory    |

---

## Summary

**Transaction Subtype** tracks the detailed nature of inventory movements:

- **ADD Type**: `purchase_stock` (buy) or `sales_return` (return received)
- **DEDUCT Type**: `sales` (sell) or `purchase_return` (return given)

**Filter System** currently supports:

- Warehouse selection (Super Admin)
- Time period selection
- Custom date ranges
- Pagination

The red box in the screenshot likely indicates where additional filters (like transaction subtype) could be added for more granular reporting.
