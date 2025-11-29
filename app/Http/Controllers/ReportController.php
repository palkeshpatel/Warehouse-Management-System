<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouseId = $user->isSuperAdmin() ? $request->warehouse_id : $user->warehouse_id;
        $period = $request->period ?? 'monthly';

        $data = $this->getReportData($warehouseId, $period, $user->isSuperAdmin());
        $warehouses = $user->isSuperAdmin() ? Warehouse::where('status', 'active')->get() : collect();

        return view('reports.index', compact('data', 'warehouses', 'period', 'warehouseId'));
    }

    private function getReportData($warehouseId, $period, $isSuperAdmin)
    {
        $query = InventoryTransaction::with(['model', 'warehouse', 'creator']);

        if (!$isSuperAdmin || $warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $dateRange = $this->getDateRange($period);
        $query->whereBetween('created_at', $dateRange);

        return [
            'transactions' => $query->latest()->get(),
            'totalAdded' => $query->where('type', 'add')->sum('qty'),
            'totalDeducted' => $query->where('type', 'deduct')->sum('qty'),
            'totalTransferred' => $query->where('type', 'transfer')->sum('qty'),
            'period' => $period,
            'dateRange' => $dateRange,
        ];
    }

    private function getDateRange($period)
    {
        return match($period) {
            'daily' => [now()->startOfDay(), now()->endOfDay()],
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'monthly' => [now()->startOfMonth(), now()->endOfMonth()],
            'quarterly' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'yearly' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}

