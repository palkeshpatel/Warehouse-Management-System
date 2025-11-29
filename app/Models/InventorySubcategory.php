<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySubcategory extends Model
{
    protected $table = 'inventory_subcategories';

    protected $fillable = ['category_id', 'name'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function models(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'subcategory_id');
    }
}
