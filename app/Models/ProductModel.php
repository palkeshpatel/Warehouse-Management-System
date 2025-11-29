<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModel extends Model
{
    protected $table = 'models';

    protected $fillable = ['subcategory_id', 'model_name'];

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(InventorySubcategory::class, 'subcategory_id');
    }

    public function inventoryStock(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'model_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'model_id');
    }
}
