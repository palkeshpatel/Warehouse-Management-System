@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Inventory Reports</h2>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Reports</h5>
    </div>
    <div class="card-body">
        <form id="reportFilterForm" class="row g-3">
            @if(auth()->user()->isSuperAdmin())
            <div class="col-md-3">
                <label class="form-label">Warehouse</label>
                <select name="warehouse_id" class="form-select" id="warehouseFilter">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ $warehouseId == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select name="period" class="form-select" id="periodFilter">
                    <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="quarterly" {{ $period == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                    <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>Total Added</h5>
                <h2>{{ number_format($data['totalAdded'] ?? 0) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5>Total Deducted</h5>
                <h2>{{ number_format($data['totalDeducted'] ?? 0) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5>Total Transferred</h5>
                <h2>{{ number_format($data['totalTransferred'] ?? 0) }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Transaction Reports</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Model</th>
                    <th>Warehouse</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>User</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['transactions'] as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaction->model->model_name }}</td>
                    <td>{{ $transaction->warehouse->name }}</td>
                    <td>
                        <span class="badge bg-{{ $transaction->type == 'add' ? 'success' : ($transaction->type == 'deduct' ? 'danger' : 'info') }}">
                            {{ ucfirst($transaction->type) }}
                        </span>
                    </td>
                    <td>{{ $transaction->qty }}</td>
                    <td>{{ $transaction->creator->name }}</td>
                    <td>{{ $transaction->remarks }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No transactions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#reportFilterForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    window.location.href = '/reports?' + formData;
});
</script>
@endpush

