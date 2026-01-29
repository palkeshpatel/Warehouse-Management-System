<table class="table table-striped dt-responsive nowrap" style="width:100%" id="reportsTable">
    <thead>
        <tr>
            <th>Date</th>
            <th>Model</th>
            <th>Warehouse</th>
            <th>Type</th>
            <th>Transaction Type</th>
            <th>Quantity</th>
            <th>User</th>
            <th>Invoice No</th>
            <th>Invoice Date</th>
            <th>Invoice File</th>
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
            <td>
                @if($transaction->transaction_subtype)
                    @php
                        $subtype = str_replace('_', ' ', $transaction->transaction_subtype);
                        $subtype = ucwords($subtype);
                    @endphp
                    <span class="badge bg-secondary">{{ $subtype }}</span>
                @else
                    -
                @endif
            </td>
            <td>{{ $transaction->qty }}</td>
            <td>{{ $transaction->creator->name ?? 'N/A' }}</td>
            <td>{{ $transaction->invoice_no ?? '-' }}</td>
            <td>{{ $transaction->invoice_date ? \Carbon\Carbon::parse($transaction->invoice_date)->format('d/m/Y') : '-' }}</td>
            <td>
                @if($transaction->invoice_path)
                    <a href="{{ asset('storage/' . $transaction->invoice_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-text"></i> View
                    </a>
                @else
                    -
                @endif
            </td>
            <td>{{ $transaction->remarks ?? '-' }}</td>
        </tr>
        @empty
        @endforelse
    </tbody>
</table>
<div class="mt-3">
    @if(isset($transactions) && method_exists($transactions, 'hasPages') && $transactions->hasPages())
    <div class="d-flex justify-content-between align-items-center">
        <div>
            Showing <strong>{{ $transactions->firstItem() }}</strong> to
            <strong>{{ $transactions->lastItem() }}</strong> of <strong>{{ $transactions->total() }}</strong> results
        </div>
        <div>
            {!! $transactions->appends(request()->except('page'))->links('pagination::bootstrap-5') !!}
        </div>
    </div>
    @endif
</div>

