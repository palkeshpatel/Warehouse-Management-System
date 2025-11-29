<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index()
    {
        $categories = InventoryCategory::with(['subcategories.models'])->latest()->get();
        return view('masters.index', compact('categories'));
    }
}

