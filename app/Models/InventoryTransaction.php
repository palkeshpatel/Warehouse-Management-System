<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $table = 'inventory_transactions';

    protected $fillable = [
        'model_id',
        'warehouse_id',
        'qty',
        'type',
        'transaction_subtype',
        'sales_return_reason_id',
        'purchase_return_reason_id',
        'reason_other',
        'invoice_path',
        'invoice_no',
        'invoice_date',
        'created_by',
        'remarks',
        'transfer_from_warehouse_id',
        'transfer_to_warehouse_id',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'model_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class, 'inventory_transaction_id');
    }

    public function transferFrom(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'transfer_from_warehouse_id');
    }

    public function transferTo(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'transfer_to_warehouse_id');
    }

    public function salesReturnReason(): BelongsTo
    {
        return $this->belongsTo(SalesReturnReason::class);
    }

    public function purchaseReturnReason(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturnReason::class);
    }
}
