<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryTransactionsExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouses = $user->isSuperAdmin() ? Warehouse::where('status', 'active')->get() : collect();

        // Initial load - return view with default data (monthly)
        if (!$request->ajax()) {
            // For Admin/Employee, use their warehouse_id; for Super Admin, allow selection
            $warehouseId = $user->isSuperAdmin() ? null : $user->warehouse_id;
            $period = 'monthly';
            $dateRange = $this->getDateRange($period);
            $startDate = $dateRange[0]->format('Y-m-d');
            $endDate = $dateRange[1]->format('Y-m-d');
            $data = $this->getReportData($warehouseId, $period, $user->isSuperAdmin(), $startDate, $endDate, 1, null);

            return view('reports.index', compact('data', 'warehouses', 'period', 'warehouseId', 'startDate', 'endDate'));
        }

        // AJAX request - return JSON
        return $this->filterReports($request);
    }

    public function filterReports(Request $request)
    {
        $user = auth()->user();
        $warehouseId = $user->isSuperAdmin() ? ($request->warehouse_id ?: null) : $user->warehouse_id;
        $period = $request->period ?? 'monthly';
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $transactionSubtype = $request->transaction_subtype ?: null;

        // If period is custom, validate dates
        if ($period === 'custom' && (!$startDate || !$endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select both start date and end date'
            ], 422);
        }

        $data = $this->getReportData($warehouseId, $period, $user->isSuperAdmin(), $startDate, $endDate, $request->page ?? 1, $transactionSubtype);

        // Build pagination URLs with current filters
        $paginator = $data['transactions'];
        $paginator->appends($request->except('page'));

        return response()->json([
            'success' => true,
            'data' => $data,
            'html' => view('reports.partials.transactions_table', [
                'transactions' => $paginator,
                'filters' => $request->except('page')
            ])->render(),
            'stats' => [
                'totalAdded' => number_format($data['totalAdded']),
                'totalDeducted' => number_format($data['totalDeducted']),
                'totalTransferred' => number_format($data['totalTransferred']),
            ]
        ]);
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $warehouseId = $user->isSuperAdmin() ? ($request->warehouse_id ?: null) : $user->warehouse_id;
        $period = $request->period ?? 'monthly';
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $transactionSubtype = $request->transaction_subtype ?: null;

        $query = $this->getReportQuery($warehouseId, $period, $user->isSuperAdmin(), $startDate, $endDate, $transactionSubtype);

        return Excel::download(new InventoryTransactionsExport($query), 'inventory_report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    private function getReportQuery($warehouseId, $period, $isSuperAdmin, $startDate = null, $endDate = null, $transactionSubtype = null)
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

        // Filter by transaction subtype
        if ($transactionSubtype) {
            $query->where('transaction_subtype', $transactionSubtype);
        }

        return $query->latest('created_at');
    }

    private function getReportData($warehouseId, $period, $isSuperAdmin, $startDate = null, $endDate = null, $page = 1, $transactionSubtype = null)
    {
        $query = $this->getReportQuery($warehouseId, $period, $isSuperAdmin, $startDate, $endDate, $transactionSubtype);
        
        // Clone query for stats calculation (need to remove latest ordering for sum efficiency if possible, but keep it simple)
        $statsQuery = InventoryTransaction::query(); // Re-build base query for stats to avoid order by issues? Or just clone.
        // Actually, re-using getReportQuery includes latest(), which doesn't affect sum().
        // But getReportQuery returns the builder.
        
        // Re-implement getReportData using getReportQuery would be cleaner but I'll stick to minimum changes to avoid breaking.
        // Wait, I should use getReportQuery inside getReportData to avoid code duplication.
        
        // Clone query for stats calculation
        $statsQuery = clone $query;
        $paginatedQuery = clone $query;

        // Get paginated transactions
        $transactions = $paginatedQuery->paginate(10, ['*'], 'page', $page);

        // Calculate stats from original query - remove ordering for stats
        $statsQuery->reorder();
        
        $totalAdded = (clone $statsQuery)->where('type', 'add')->sum('qty');
        $totalDeducted = (clone $statsQuery)->where('type', 'deduct')->sum('qty');
        $totalTransferred = (clone $statsQuery)->where('type', 'transfer')->sum('qty');

        return [
            'transactions' => $transactions,
            'totalAdded' => $totalAdded,
            'totalDeducted' => $totalDeducted,
            'totalTransferred' => $totalTransferred,
            'period' => $period,
            'dateRange' => $period === 'custom' && $startDate && $endDate ? 
                [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()] : 
                $this->getDateRange($period),
        ];
    }

    private function getDateRange($period)
    {
        return match ($period) {
            'daily' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'weekly' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'quarterly' => [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()],
            'yearly' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }
}
