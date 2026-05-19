<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    protected $table = 'inventory_logs';

    protected $fillable = [
        'inventory_transaction_id',
        'user_id',
        'action',
        'old_qty',
        'new_qty',
        'old_model_id',
        'new_model_id',
        'old_warehouse_id',
        'new_warehouse_id',
        'old_transaction_subtype',
        'new_transaction_subtype',
        'old_invoice_no',
        'new_invoice_no',
        'old_invoice_date',
        'new_invoice_date',
        'old_invoice_path',
        'new_invoice_path',
        'old_remarks',
        'new_remarks',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class, 'inventory_transaction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function oldModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'old_model_id');
    }

    public function newModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'new_model_id');
    }

    public function oldWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'old_warehouse_id');
    }

    public function newWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'new_warehouse_id');
    }
}
