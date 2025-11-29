<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryStock extends Model
{
    protected $table = 'inventory_stock';

    protected $fillable = [
        'model_id',
        'warehouse_id',
        'total_stock',
        'available_stock',
        'created_by',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'model_id', 'model_id')
            ->where('warehouse_id', $this->warehouse_id);
    }

    public function latestAddTransaction()
    {
        return $this->hasOne(InventoryTransaction::class, 'model_id', 'model_id')
            ->where('warehouse_id', $this->warehouse_id)
            ->where('type', 'add')
            ->latest('created_at');
    }
}
