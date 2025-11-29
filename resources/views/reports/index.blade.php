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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Filter
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
                                <td>{{ $transaction->model->model_name ?? 'N/A' }}</td>
                                <td>{{ $transaction->warehouse->name ?? 'N/A' }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $transaction->type == 'add' ? 'success' : ($transaction->type == 'deduct' ? 'danger' : 'info') }}">
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let startDatePicker, endDatePicker;

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
                            confirmButtonColor: '#FF9900'
                        });
                        return;
                    }

                    if (new Date(startDate) > new Date(endDate)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validation Error',
                            text: 'Start date cannot be greater than end date',
                            confirmButtonColor: '#FF9900'
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
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr);
                    }
                });
            }
        });
    </script>
@endpush
