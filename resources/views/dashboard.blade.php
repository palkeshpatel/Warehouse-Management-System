@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container py-4">
        @if (auth()->user()->hasRole('super-admin'))
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Total Inventory</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="mb-0">{{ number_format($totalInventory ?? 0) }}</h2>
                            <small class="text-muted">All Warehouses</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Today Added</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="mb-0">{{ number_format($todayAdded ?? 0) }}</h2>
                            <small class="text-muted">Items added today</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-dash-circle me-2"></i>Today Deducted</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="mb-0">{{ number_format($todayDeducted ?? 0) }}</h2>
                            <small class="text-muted">Items deducted today</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-building me-2"></i>Warehouses</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="mb-0">{{ number_format($totalWarehouses ?? 0) }}</h2>
                            <small class="text-muted">Active warehouses</small>
                        </div>
                    </div>
                </div>
            </div>

            @if (isset($warehouses) && $warehouses->count() > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Warehouse Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($warehouses as $warehouse)
                                        <div class="col-md-4 mb-3">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="text-primary mb-0">{{ $warehouse->name }}</h6>
                                                </div>
                                                <p class="text-muted mb-2"><i
                                                        class="bi bi-geo-alt me-2"></i>{{ $warehouse->location }}</p>
                                                <small class="text-muted d-block mb-3">{{ $warehouse->address }}</small>

                                                <div class="row g-2 mt-2">
                                                    <div class="col-12">
                                                        <div class="bg-light rounded p-2">
                                                            <small class="text-muted d-block">Total Inventory</small>
                                                            <strong
                                                                class="text-primary">{{ number_format($warehouse->total_inventory ?? 0) }}</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="bg-success bg-opacity-10 rounded p-2">
                                                            <small class="text-muted d-block">Today Added</small>
                                                            <strong
                                                                class="text-success">{{ number_format($warehouse->today_added ?? 0) }}</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="bg-danger bg-opacity-10 rounded p-2">
                                                            <small class="text-muted d-block">Today Deducted</small>
                                                            <strong
                                                                class="text-danger">{{ number_format($warehouse->today_deducted ?? 0) }}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @elseif(auth()->user()->hasRole(['admin', 'employee']))
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Total Inventory</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="mb-0">{{ number_format($totalInventory ?? 0) }}</h2>
                            <small class="text-muted">Your Warehouse</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Today Added</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="mb-0">{{ number_format($todayAdded ?? 0) }}</h2>
                            <small class="text-muted">Items added today</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-dash-circle me-2"></i>Today Deducted</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="mb-0">{{ number_format($todayDeducted ?? 0) }}</h2>
                            <small class="text-muted">Items deducted today</small>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Inventory</h5>
                        </div>
                        <div class="card-body text-center">
                            <a href="{{ route('inventory.index') }}" class="btn btn-primary btn-lg">Go to Inventory</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-dash-circle me-2"></i>Deduct Inventory</h5>
                        </div>
                        <div class="card-body text-center">
                            <a href="{{ route('inventory.index') }}" class="btn btn-danger btn-lg">Go to Inventory</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (isset($lowStockAlerts) && $lowStockAlerts->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h5><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alerts</h5>
                        <ul class="mb-0">
                            @foreach ($lowStockAlerts as $alert)
                                <li>
                                    <strong>{{ $alert->model->model_name }}</strong> -
                                    {{ number_format($alert->available_stock) }} units remaining
                                    @if (auth()->user()->hasRole('super-admin'))
                                        ({{ $alert->warehouse->name }})
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
