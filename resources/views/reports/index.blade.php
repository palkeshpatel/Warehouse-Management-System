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
                @if (auth()->user()->isSuperAdmin())
                    <div class="col-md-2">
                        <label class="form-label">Warehouse</label>
                        <select name="warehouse_id" class="form-select" id="warehouseFilter">
                            <option value="">All Warehouses</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select" id="periodFilter">
                        <option value="daily" {{ ($period ?? 'monthly') == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ ($period ?? 'monthly') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ ($period ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ ($period ?? 'monthly') == 'quarterly' ? 'selected' : '' }}>Quarterly
                        </option>
                        <option value="yearly" {{ ($period ?? 'monthly') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="custom" {{ ($period ?? 'monthly') == 'custom' ? 'selected' : '' }}>Custom Date Range
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Transaction Type</label>
                    <select name="transaction_subtype" class="form-select" id="transactionSubtypeFilter">
                        <option value="">All Types</option>
                        <option value="purchase_stock">Purchase Stock</option>
                        <option value="sales_return">Sales Return</option>
                        <option value="sales">Sales</option>
                        <option value="purchase_return">Purchase Return</option>
                    </select>
                </div>
                <div class="col-md-2" id="startDateContainer" style="display: none;">
                    <label class="form-label">Start Date</label>
                    <input type="text" name="start_date" class="form-control" id="startDate"
                        value="{{ $startDate ?? date('Y-m-d', strtotime('-30 days')) }}" placeholder="Select Start Date"
                        readonly>
                </div>
                <div class="col-md-2" id="endDateContainer" style="display: none;">
                    <label class="form-label">End Date</label>
                    <input type="text" name="end_date" class="form-control" id="endDate"
                        value="{{ $endDate ?? date('Y-m-d') }}" placeholder="Select End Date" readonly>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" title="Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <button type="button" id="downloadExcelBtn" class="btn btn-success flex-grow-1" title="Download Excel">
                        <i class="bi bi-file-earmark-excel"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5>Total Added</h5>
                    <h2 id="totalAdded">{{ number_format($data['totalAdded'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5>Total Deducted</h5>
                    <h2 id="totalDeducted">{{ number_format($data['totalDeducted'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5>Total Transferred</h5>
                    <h2 id="totalTransferred">{{ number_format($data['totalTransferred'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Transaction Reports</h5>
        </div>
        <div class="card-body" id="transactionsContainer">
            <div id="transactionsTable">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['transactions'] as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $transaction->model->model_name ?? 'N/A' }}</td>
                                <td>{{ $transaction->warehouse->name ?? 'N/A' }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $transaction->type == 'add' ? 'success' : ($transaction->type == 'deduct' ? 'danger' : 'info') }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($transaction->transaction_subtype)
                                        @php
                                            $subtype = str_replace('_', ' ', $transaction->transaction_subtype);
                                            $subtype = ucwords($subtype);
                                        @endphp
                                        <span class="badge bg-secondary">{{ $subtype }}</span>
                                    @else
                                        <span class="text-muted">-</span>
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
                                <td>
                                    @if(auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $transaction->warehouse_id == auth()->user()->warehouse_id))
                                        <button class="btn btn-sm btn-warning btn-edit-transaction" data-id="{{ $transaction->id }}">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No transactions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if (isset($data['transactions']) &&
                        method_exists($data['transactions'], 'hasPages') &&
                        $data['transactions']->hasPages())
                    <div class="pagination-info">
                        <div>
                            Showing <strong>{{ $data['transactions']->firstItem() }}</strong> to
                            <strong>{{ $data['transactions']->lastItem() }}</strong> of
                            <strong>{{ $data['transactions']->total() }}</strong> results
                        </div>
                        <div>
                            {{ $data['transactions']->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Edit Inventory Transaction Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" id="editModalHeader">
                    <h5 class="modal-title" id="editTransactionModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Inventory Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTransactionForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="editTransactionId">
                    <div class="modal-body">
                        <!-- Transaction Type Info Alert -->
                        <div class="alert alert-info mb-3 d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                <strong>Type:</strong> <span id="editTransactionTypeDisplay" class="text-uppercase font-monospace fw-bold"></span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- Warehouse fields -->
                            <!-- For Add/Deduct: Warehouse (Super Admin only, otherwise hidden/disabled) -->
                            <div class="col-md-6" id="editWarehouseGroup">
                                <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                                @if(auth()->user()->isSuperAdmin())
                                    <select name="warehouse_id" class="form-select" id="editWarehouse" required>
                                        @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="warehouse_id" id="editWarehouseHidden">
                                    <input type="text" class="form-control" id="editWarehouseName" readonly>
                                @endif
                            </div>

                            <!-- For Transfer: From Warehouse & To Warehouse -->
                            <div class="col-md-6" id="editFromWarehouseGroup" style="display: none;">
                                <label class="form-label">From Warehouse <span class="text-danger">*</span></label>
                                @if(auth()->user()->isSuperAdmin())
                                    <select name="from_warehouse_id" class="form-select" id="editFromWarehouse" required>
                                        @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="from_warehouse_id" id="editFromWarehouseHidden">
                                    <input type="text" class="form-control" id="editFromWarehouseName" readonly>
                                @endif
                            </div>

                            <div class="col-md-6" id="editToWarehouseGroup" style="display: none;">
                                <label class="form-label">To Warehouse <span class="text-danger">*</span></label>
                                @if(auth()->user()->isSuperAdmin())
                                    <select name="to_warehouse_id" class="form-select" id="editToWarehouse" required>
                                        @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select name="to_warehouse_id" class="form-select" id="editToWarehouseAdmin" required>
                                        @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                            @if($warehouse->id != auth()->user()->warehouse_id)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            <!-- Category, Subcategory, Model Selection (Editable for all) -->
                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" id="editCategory" required>
                                    <option value="">Select Category</option>
                                    @foreach (\App\Models\InventoryCategory::all() as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subcategory <span class="text-danger">*</span></label>
                                <select name="subcategory_id" class="form-select" id="editSubcategory" required disabled>
                                    <option value="">Select Subcategory</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Model <span class="text-danger">*</span></label>
                                <select name="model_id" class="form-select" id="editModel" required disabled>
                                    <option value="">Select Model</option>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-6">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="qty" class="form-control" id="editQty" min="1" required>
                                <div class="form-text text-muted" id="editQtyHelp">Enter the corrected quantity.</div>
                            </div>

                            <!-- Transaction Subtype Section (For Add/Deduct only) -->
                            <div class="col-12" id="editSubtypeGroup">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Transaction Subtype <span class="text-danger">*</span></label>
                                        <select name="transaction_subtype" class="form-select" id="editTransactionSubtype">
                                            <!-- Subtype options loaded dynamically based on type -->
                                        </select>
                                    </div>
                                    <!-- Return Reason (for Sales Return or Purchase Return) -->
                                    <div class="col-md-6" id="editReturnReasonGroup" style="display: none;">
                                        <label class="form-label">Return Reason <span class="text-danger">*</span></label>
                                        <select name="sales_return_reason_id" class="form-select" id="editSalesReturnReason">
                                            <option value="">Select Reason</option>
                                        </select>
                                        <select name="purchase_return_reason_id" class="form-select" id="editPurchaseReturnReason" style="display: none;">
                                            <option value="">Select Reason</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-2" id="editReasonOtherGroup" style="display: none;">
                                    <div class="col-12">
                                        <label class="form-label">Specify Other Reason <span class="text-danger">*</span></label>
                                        <textarea name="reason_other" id="editReasonOther" class="form-control" rows="2" placeholder="Please specify the reason"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Details Section (For Add/Deduct only) -->
                            <div class="col-12" id="editInvoiceGroup">
                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3 text-primary"><i class="bi bi-receipt me-2"></i>Invoice Details (Optional)</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Invoice No</label>
                                                <input type="text" name="invoice_no" id="editInvoiceNo" class="form-control" placeholder="Enter Invoice No">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Invoice Date</label>
                                                <input type="date" name="invoice_date" id="editInvoiceDate" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Invoice File</label>
                                                <input type="file" name="invoice" class="form-control" accept=".jpg,.jpeg,.pdf">
                                                <small class="text-muted d-block mt-1">Leave empty to keep existing file.</small>
                                                <div id="editCurrentInvoiceFile" class="mt-2" style="display: none;">
                                                    <span class="badge bg-secondary">Current File:</span>
                                                    <a href="#" id="editInvoiceFileLink" target="_blank" class="btn btn-sm btn-link p-0 text-decoration-none">
                                                        <i class="bi bi-file-earmark-text"></i> View Current
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks (Always shown) -->
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" id="editRemarks" class="form-control" rows="3" placeholder="Enter remarks (optional)"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveTransaction">
                            <i class="bi bi-check-circle me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            let startDatePicker, endDatePicker;
            let reportsTable;

            function initDataTable() {
                if ($.fn.DataTable.isDataTable('#reportsTable')) {
                    $('#reportsTable').DataTable().destroy();
                }
                
                reportsTable = $('#reportsTable').DataTable({
                    responsive: true,
                    autoWidth: false,
                    paging: false, // Using Laravel pagination
                    searching: false, // Using custom filters
                    info: false, // Using custom info
                    ordering: false, // Preserving backend order
                    columnDefs: [
                        { responsivePriority: 1, targets: 0 }, // Date
                        { responsivePriority: 2, targets: 3 }, // Type
                        { responsivePriority: 3, targets: 5 }, // Quantity
                        { responsivePriority: 4, targets: -1 } // Remarks
                    ]
                });
            }

            // Initialize on load
            initDataTable();

            // Initialize Flatpickr Date Pickers
            function initDatePickers() {
                if (startDatePicker) startDatePicker.destroy();
                if (endDatePicker) endDatePicker.destroy();

                startDatePicker = flatpickr("#startDate", {
                    dateFormat: "Y-m-d",
                    maxDate: "today",
                    onChange: function(selectedDates, dateStr, instance) {
                        if (endDatePicker) {
                            endDatePicker.set("minDate", dateStr);
                        }
                    }
                });

                endDatePicker = flatpickr("#endDate", {
                    dateFormat: "Y-m-d",
                    maxDate: "today",
                    minDate: $("#startDate").val() || null
                });
            }

            // Period filter change handler
            $('#periodFilter').on('change', function() {
                const period = $(this).val();

                if (period === 'custom') {
                    $('#startDateContainer, #endDateContainer').show();
                    $('#startDate, #endDate').prop('required', true);
                    // Reinitialize datepickers for custom selection
                    initDatePickers();
                } else {
                    $('#startDateContainer, #endDateContainer').hide();
                    $('#startDate, #endDate').prop('required', false);

                    // Set dates based on period
                    const dates = getPeriodDates(period);
                    $('#startDate').val(dates.start);
                    $('#endDate').val(dates.end);
                }
            });

            // Set initial state
            if ($('#periodFilter').val() !== 'custom') {
                $('#startDateContainer, #endDateContainer').hide();
            } else {
                $('#startDateContainer, #endDateContainer').show();
                initDatePickers();
            }

            // Function to get dates based on period
            function getPeriodDates(period) {
                const today = new Date();
                let start, end;

                switch (period) {
                    case 'daily':
                        start = new Date(today);
                        end = new Date(today);
                        break;
                    case 'weekly':
                        start = new Date(today);
                        start.setDate(today.getDate() - today.getDay()); // Start of week
                        end = new Date(today);
                        end.setDate(start.getDate() + 6); // End of week
                        break;
                    case 'monthly':
                        start = new Date(today.getFullYear(), today.getMonth(), 1);
                        end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                        break;
                    case 'quarterly':
                        const quarter = Math.floor(today.getMonth() / 3);
                        start = new Date(today.getFullYear(), quarter * 3, 1);
                        end = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
                        break;
                    case 'yearly':
                        start = new Date(today.getFullYear(), 0, 1);
                        end = new Date(today.getFullYear(), 11, 31);
                        break;
                    default:
                        start = new Date(today);
                        end = new Date(today);
                }

                return {
                    start: formatDate(start),
                    end: formatDate(end)
                };
            }

            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // Handle pagination links via AJAX
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (url) {
                    try {
                        const urlObj = new URL(url);
                        const page = urlObj.searchParams.get('page') || 1;
                        loadReports(page);
                    } catch (e) {
                        // If URL parsing fails, try to extract page from href
                        const match = url.match(/[?&]page=(\d+)/);
                        const page = match ? match[1] : 1;
                        loadReports(page);
                    }
                }
            });

            // Form submission
            $('#reportFilterForm').on('submit', function(e) {
                e.preventDefault();
                loadReports(1);
            });

            // Export Excel
            $('#downloadExcelBtn').on('click', function() {
                const formData = $('#reportFilterForm').serialize();
                window.location.href = "{{ route('reports.export') }}?" + formData;
            });

            function loadReports(page = 1) {
                const period = $('#periodFilter').val();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();

                // Validate dates only if custom period is selected
                if (period === 'custom') {
                    if (!startDate || !endDate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validation Error',
                            text: 'Please select both start date and end date',
                            confirmButtonColor: '#601d57'
                        });
                        return;
                    }

                    if (new Date(startDate) > new Date(endDate)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validation Error',
                            text: 'Start date cannot be greater than end date',
                            confirmButtonColor: '#601d57'
                        });
                        return;
                    }
                }

                const formData = $('#reportFilterForm').serialize() + '&page=' + page;

                $.ajax({
                    url: '/reports/filter',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Update stats
                            $('#totalAdded').text(response.stats.totalAdded);
                            $('#totalDeducted').text(response.stats.totalDeducted);
                            $('#totalTransferred').text(response.stats.totalTransferred);

                            // Update table with pagination
                            $('#transactionsContainer').html(response.html);
                            
                            // Reinitialize DataTable
                            initDataTable();
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr);
                    }
                });
            }

            // --- EDIT TRANSACTION LOGIC ---
            
            // Handle clicking the Edit button
            $(document).on('click', '.btn-edit-transaction', function() {
                const id = $(this).data('id');
                
                // Show loading state
                Swal.fire({
                    title: 'Loading transaction...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '/inventory/transactions/' + id + '/edit',
                    method: 'GET',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            const transaction = response.transaction;
                            
                            // Set IDs
                            $('#editTransactionId').val(transaction.id);
                            
                            // Setup Header Style based on Type
                            const header = $('#editModalHeader');
                            header.removeClass('bg-primary bg-danger bg-warning text-white text-dark');
                            $('#editTransactionTypeDisplay').text(transaction.type);
                            
                            if (transaction.type === 'add') {
                                header.addClass('bg-primary text-white');
                                $('#editWarehouseGroup').show();
                                $('#editFromWarehouseGroup').hide();
                                $('#editToWarehouseGroup').hide();
                                $('#editSubtypeGroup').show();
                                $('#editInvoiceGroup').show();
                                
                                // Populate warehouses
                                if ($('#editWarehouse').length) {
                                    $('#editWarehouse').val(transaction.warehouse_id).prop('disabled', false);
                                } else {
                                    $('#editWarehouseHidden').val(transaction.warehouse_id);
                                    $('#editWarehouseName').val(transaction.warehouse ? transaction.warehouse.name : 'Unknown');
                                }
                                
                                // Populate Subtypes
                                const subtypeSelect = $('#editTransactionSubtype');
                                subtypeSelect.html(`
                                    <option value="purchase_stock">Purchase Stock</option>
                                    <option value="sales_return">Return (Sales)</option>
                                `);
                                subtypeSelect.val(transaction.transaction_subtype);
                                
                                // Handle Sales Return Reasons
                                const reasonSelect = $('#editSalesReturnReason');
                                reasonSelect.html('<option value="">Select Reason</option>').show();
                                $('#editPurchaseReturnReason').hide();
                                
                                response.sales_return_reasons.forEach(function(r) {
                                    reasonSelect.append(`<option value="${r.id}" data-is-other="${r.name.toLowerCase() === 'other' ? '1' : '0'}">${r.name}</option>`);
                                });
                                
                                if (transaction.transaction_subtype === 'sales_return') {
                                    $('#editReturnReasonGroup').show();
                                    reasonSelect.val(transaction.sales_return_reason_id);
                                    const selectedOpt = reasonSelect.find('option:selected');
                                    if (selectedOpt.data('is-other') == '1') {
                                        $('#editReasonOtherGroup').show();
                                        $('#editReasonOther').val(transaction.reason_other).prop('required', true);
                                    } else {
                                        $('#editReasonOtherGroup').hide();
                                        $('#editReasonOther').val('').prop('required', false);
                                    }
                                } else {
                                    $('#editReturnReasonGroup').hide();
                                    $('#editReasonOtherGroup').hide();
                                    $('#editReasonOther').val('').prop('required', false);
                                }
                                
                            } else if (transaction.type === 'deduct') {
                                header.addClass('bg-danger text-white');
                                $('#editWarehouseGroup').show();
                                $('#editFromWarehouseGroup').hide();
                                $('#editToWarehouseGroup').hide();
                                $('#editSubtypeGroup').show();
                                $('#editInvoiceGroup').show();
                                
                                // Populate warehouses
                                if ($('#editWarehouse').length) {
                                    $('#editWarehouse').val(transaction.warehouse_id).prop('disabled', false);
                                } else {
                                    $('#editWarehouseHidden').val(transaction.warehouse_id);
                                    $('#editWarehouseName').val(transaction.warehouse ? transaction.warehouse.name : 'Unknown');
                                }
                                
                                // Populate Subtypes
                                const subtypeSelect = $('#editTransactionSubtype');
                                subtypeSelect.html(`
                                    <option value="sales">Sales</option>
                                    <option value="purchase_return">Return (Purchase)</option>
                                `);
                                subtypeSelect.val(transaction.transaction_subtype);
                                
                                // Handle Purchase Return Reasons
                                const reasonSelect = $('#editPurchaseReturnReason');
                                reasonSelect.html('<option value="">Select Reason</option>').show();
                                $('#editSalesReturnReason').hide();
                                
                                response.purchase_return_reasons.forEach(function(r) {
                                    reasonSelect.append(`<option value="${r.id}" data-is-other="${r.name.toLowerCase() === 'other' ? '1' : '0'}">${r.name}</option>`);
                                });
                                
                                if (transaction.transaction_subtype === 'purchase_return') {
                                    $('#editReturnReasonGroup').show();
                                    reasonSelect.val(transaction.purchase_return_reason_id);
                                    const selectedOpt = reasonSelect.find('option:selected');
                                    if (selectedOpt.data('is-other') == '1') {
                                        $('#editReasonOtherGroup').show();
                                        $('#editReasonOther').val(transaction.reason_other).prop('required', true);
                                    } else {
                                        $('#editReasonOtherGroup').hide();
                                        $('#editReasonOther').val('').prop('required', false);
                                    }
                                } else {
                                    $('#editReturnReasonGroup').hide();
                                    $('#editReasonOtherGroup').hide();
                                    $('#editReasonOther').val('').prop('required', false);
                                }
                                
                            } else if (transaction.type === 'transfer') {
                                header.addClass('bg-warning text-dark');
                                $('#editWarehouseGroup').hide();
                                $('#editFromWarehouseGroup').show();
                                $('#editToWarehouseGroup').show();
                                $('#editSubtypeGroup').hide();
                                $('#editInvoiceGroup').hide();
                                
                                // Populate From/To warehouses
                                if ($('#editFromWarehouse').length) {
                                    $('#editFromWarehouse').val(transaction.transfer_from_warehouse_id).prop('disabled', false);
                                    $('#editToWarehouse').val(transaction.transfer_to_warehouse_id);
                                } else {
                                    $('#editFromWarehouseHidden').val(transaction.transfer_from_warehouse_id);
                                    $('#editFromWarehouseName').val(transaction.transfer_from ? transaction.transfer_from.name : 'Unknown');
                                    $('#editToWarehouseAdmin').val(transaction.transfer_to_warehouse_id);
                                }
                            }
                            
                            // Populate Category, Subcategory, Model dropdowns
                            $('#editCategory').val(response.category_id);
                            
                            const subSelect = $('#editSubcategory');
                            subSelect.html('<option value="">Select Subcategory</option>');
                            response.subcategories.forEach(function(sub) {
                                subSelect.append(`<option value="${sub.id}">${sub.name}</option>`);
                            });
                            subSelect.val(response.subcategory_id).prop('disabled', false);
                            
                            const modelSelect = $('#editModel');
                            modelSelect.html('<option value="">Select Model</option>');
                            response.models.forEach(function(m) {
                                modelSelect.append(`<option value="${m.id}">${m.model_name}</option>`);
                            });
                            modelSelect.val(transaction.model_id).prop('disabled', false);
                            
                            // Populate Qty and Remarks
                            $('#editQty').val(transaction.qty);
                            $('#editRemarks').val(transaction.remarks);
                            
                            // Invoice Details
                            $('#editInvoiceNo').val(transaction.invoice_no);
                            $('#editInvoiceDate').val(transaction.invoice_date);
                            
                            if (transaction.invoice_path) {
                                $('#editInvoiceFileLink').attr('href', '/storage/' + transaction.invoice_path);
                                $('#editCurrentInvoiceFile').show();
                            } else {
                                $('#editCurrentInvoiceFile').hide();
                            }
                            
                            // Open Modal
                            $('#editTransactionModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        handleAjaxError(xhr);
                    }
                });
            });
            
            // Subtype change handler in Edit Modal
            $('#editTransactionSubtype').on('change', function() {
                const val = $(this).val();
                if (val === 'sales_return') {
                    $('#editReturnReasonGroup').show();
                    $('#editSalesReturnReason').show().prop('required', true);
                    $('#editPurchaseReturnReason').hide().prop('required', false);
                } else if (val === 'purchase_return') {
                    $('#editReturnReasonGroup').show();
                    $('#editPurchaseReturnReason').show().prop('required', true);
                    $('#editSalesReturnReason').hide().prop('required', false);
                } else {
                    $('#editReturnReasonGroup').hide();
                    $('#editSalesReturnReason').hide().prop('required', false);
                    $('#editPurchaseReturnReason').hide().prop('required', false);
                    $('#editReasonOtherGroup').hide();
                    $('#editReasonOther').val('').prop('required', false);
                }
            });
            
            // Reason change handlers to toggle "Other" reason specification
            $('#editSalesReturnReason, #editPurchaseReturnReason').on('change', function() {
                const opt = $(this).find('option:selected');
                if (opt.data('is-other') == '1') {
                    $('#editReasonOtherGroup').show();
                    $('#editReasonOther').prop('required', true);
                } else {
                    $('#editReasonOtherGroup').hide();
                    $('#editReasonOther').val('').prop('required', false);
                }
            });
            
            // Category change in Edit Modal
            $('#editCategory').on('change', function() {
                const categoryId = $(this).val();
                const subcategorySelect = $('#editSubcategory');
                const modelSelect = $('#editModel');

                subcategorySelect.html('<option value="">Loading...</option>').prop('disabled', true);
                modelSelect.html('<option value="">Select Model</option>').prop('disabled', true);

                if (categoryId) {
                    $.ajax({
                        url: '/inventory/subcategories/' + categoryId,
                        method: 'GET',
                        success: function(response) {
                            subcategorySelect.html('<option value="">Select Subcategory</option>');
                            const subcategories = response.subcategories || response;
                            if (Array.isArray(subcategories) && subcategories.length > 0) {
                                subcategories.forEach(function(sub) {
                                    subcategorySelect.append(`<option value="${sub.id}">${sub.name}</option>`);
                                });
                                subcategorySelect.prop('disabled', false);
                            } else {
                                subcategorySelect.html('<option value="">No subcategories found</option>');
                            }
                        },
                        error: function(xhr) {
                            subcategorySelect.html('<option value="">Error loading subcategories</option>');
                        }
                    });
                } else {
                    subcategorySelect.html('<option value="">Select Subcategory</option>').prop('disabled', true);
                }
            });

            // Subcategory change in Edit Modal
            $('#editSubcategory').on('change', function() {
                const subcategoryId = $(this).val();
                const modelSelect = $('#editModel');

                modelSelect.html('<option value="">Loading...</option>').prop('disabled', true);

                if (subcategoryId) {
                    $.ajax({
                        url: '/inventory/models/' + subcategoryId,
                        method: 'GET',
                        success: function(response) {
                            modelSelect.html('<option value="">Select Model</option>');
                            const models = response.models || response;
                            if (Array.isArray(models) && models.length > 0) {
                                models.forEach(function(model) {
                                    modelSelect.append(`<option value="${model.id}">${model.model_name}</option>`);
                                });
                                modelSelect.prop('disabled', false);
                            } else {
                                modelSelect.html('<option value="">No models found</option>');
                            }
                        },
                        error: function(xhr) {
                            modelSelect.html('<option value="">Error loading models</option>');
                        }
                    });
                } else {
                    modelSelect.html('<option value="">Select Model</option>').prop('disabled', true);
                }
            });
            
            // Edit form submission via AJAX
            $('#editTransactionForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#editTransactionId').val();
                const formData = new FormData(this);
                const submitBtn = $('#btnSaveTransaction');
                const originalText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
                
                $.ajax({
                    url: '/inventory/transactions/' + id + '/update',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message || 'Transaction updated successfully',
                                confirmButtonColor: '#601d57'
                            }).then(() => {
                                $('#editTransactionModal').modal('hide');
                                loadReports(); // Reload current view
                            });
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
@endpush
