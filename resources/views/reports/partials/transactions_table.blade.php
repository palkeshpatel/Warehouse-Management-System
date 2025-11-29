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
        @forelse($transactions as $transaction)
        <tr>
            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $transaction->model->model_name ?? 'N/A' }}</td>
            <td>{{ $transaction->warehouse->name ?? 'N/A' }}</td>
            <td>
                <span class="badge bg-{{ $transaction->type == 'add' ? 'success' : ($transaction->type == 'deduct' ? 'danger' : 'info') }}">
                    {{ ucfirst($transaction->type) }}
                </span>
            </td>
            <td>{{ $transaction->qty }}</td>
            <td>{{ $transaction->creator->name ?? 'N/A' }}</td>
            <td>{{ $transaction->remarks ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No transactions found
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@if(isset($transactions) && method_exists($transactions, 'hasPages') && $transactions->hasPages())
    <div class="pagination-info">
        <div>
            Showing <strong>{{ $transactions->firstItem() }}</strong> to
            <strong>{{ $transactions->lastItem() }}</strong> of <strong>{{ $transactions->total() }}</strong> results
        </div>
        <div>
            {!! $transactions->appends(request()->except('page'))->links('pagination::bootstrap-5') !!}
        </div>
    </div>
@endif

