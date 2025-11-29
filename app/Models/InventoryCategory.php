<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    protected $table = 'inventory_categories';

    protected $fillable = ['name'];

    public function subcategories(): HasMany
    {
        return $this->hasMany(InventorySubcategory::class, 'category_id');
    }
}
