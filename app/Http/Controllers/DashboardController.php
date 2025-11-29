<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $data = $this->getSuperAdminData();
        } elseif ($user->isAdmin()) {
            $data = $this->getAdminData($user->warehouse_id);
        } else {
            $data = $this->getEmployeeData($user->warehouse_id);
        }

        return view('dashboard', $data);
    }

    private function getSuperAdminData()
    {
        $today = now()->format('Y-m-d');
        $warehouses = Warehouse::where('status', 'active')->get();
        
        // Get statistics for each warehouse
        $warehousesWithStats = $warehouses->map(function ($warehouse) use ($today) {
            $warehouse->total_inventory = InventoryStock::where('warehouse_id', $warehouse->id)->sum('total_stock');
            $warehouse->today_added = InventoryTransaction::where('warehouse_id', $warehouse->id)
                ->where('type', 'add')
                ->whereDate('created_at', $today)
                ->sum('qty');
            $warehouse->today_deducted = InventoryTransaction::where('warehouse_id', $warehouse->id)
                ->where('type', 'deduct')
                ->whereDate('created_at', $today)
                ->sum('qty');
            return $warehouse;
        });

        return [
            'totalInventory' => InventoryStock::sum('total_stock'),
            'totalWarehouses' => Warehouse::where('status', 'active')->count(),
            'totalCategories' => \App\Models\InventoryCategory::count(),
            'todayAdded' => InventoryTransaction::where('type', 'add')
                ->whereDate('created_at', $today)
                ->sum('qty'),
            'todayDeducted' => InventoryTransaction::where('type', 'deduct')
                ->whereDate('created_at', $today)
                ->sum('qty'),
            'warehouses' => $warehousesWithStats,
            'lowStockAlerts' => InventoryStock::where('available_stock', '<', 10)->with(['model', 'warehouse'])->get(),
        ];
    }

    private function getAdminData($warehouseId)
    {
        $today = now()->format('Y-m-d');

        return [
            'totalInventory' => InventoryStock::where('warehouse_id', $warehouseId)->sum('total_stock'),
            'todayAdded' => InventoryTransaction::where('warehouse_id', $warehouseId)
                ->where('type', 'add')
                ->whereDate('created_at', $today)
                ->sum('qty'),
            'todayDeducted' => InventoryTransaction::where('warehouse_id', $warehouseId)
                ->where('type', 'deduct')
                ->whereDate('created_at', $today)
                ->sum('qty'),
            'lowStockAlerts' => InventoryStock::where('warehouse_id', $warehouseId)
                ->where('available_stock', '<', 10)
                ->with('model')
                ->get(),
        ];
    }

    private function getEmployeeData($warehouseId)
    {
        return [
            'warehouseId' => $warehouseId,
        ];
    }
}

