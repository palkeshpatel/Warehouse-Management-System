@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventory Management</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">Add Inventory</button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deductModal">Deduct Inventory</button>
            @if (auth()->user()->isSuperAdmin())
                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#transferModal">Transfer Stock</button>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Inventory List</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Warehouse</th>
                        <th>Total Stock</th>
                        <th>Available</th>
                        <th>Added</th>
                        @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $item)
                        <tr>
                            <td>{{ $item->model->model_name ?? 'N/A' }}</td>
                            <td>{{ $item->model->subcategory->category->name ?? 'N/A' }}</td>
                            <td>{{ $item->model->subcategory->name ?? 'N/A' }}</td>
                            <td>{{ $item->warehouse->name ?? 'N/A' }}</td>
                            <td>{{ number_format($item->total_stock) }}</td>
                            <td>{{ number_format($item->available_stock) }}</td>
                            <td>
                                <span class="badge bg-secondary" title="{{ $item->created_at->format('Y-m-d H:i:s') }}">
                                    {{ $item->created_at->diffForHumans() }}
                                </span>
                            </td>
                            @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() ? '8' : '7' }}"
                                class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No inventory found. Click "Add Inventory" to add items.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($inventory->hasPages())
                <div class="pagination-info">
                    <div>
                        Showing <strong>{{ $inventory->firstItem() }}</strong> to
                        <strong>{{ $inventory->lastItem() }}</strong> of <strong>{{ $inventory->total() }}</strong>
                        results
                    </div>
                    <div>
                        {{ $inventory->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="pagination-info">
                    <div>
                        Showing <strong>{{ $inventory->count() }}</strong> result(s)
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('inventory.modals.add')
    @include('inventory.modals.deduct')
    @if (auth()->user()->isSuperAdmin())
        @include('inventory.modals.transfer')
    @endif
@endsection

@push('scripts')
    <script>
        // Wait for document ready - jQuery is loaded by now
        $(document).ready(function() {
            console.log('Document ready - setting up Add Inventory modal handlers');

            // Bind events when modal is shown (after it's fully displayed)
            $(document).on('shown.bs.modal', '#addModal', function() {
                console.log('Modal is now shown');

                // Category change handler
                $('#addCategory').off('change').on('change', function() {
                    const categoryId = $(this).val();
                    console.log('Category changed to:', categoryId);

                    const subcategorySelect = $('#addSubcategory');
                    const modelSelect = $('#addModel');

                    subcategorySelect.html('<option value="">Loading...</option>').prop('disabled',
                        true);
                    modelSelect.html('<option value="">Select Model</option>').prop('disabled',
                        true);

                    if (categoryId) {
                        $.ajax({
                            url: '/inventory/subcategories/' + categoryId,
                            method: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                console.log('Subcategories received:', response);
                                subcategorySelect.html(
                                    '<option value="">Select Subcategory</option>');

                                let subcategories = [];
                                if (response.subcategories) {
                                    subcategories = response.subcategories;
                                } else if (Array.isArray(response)) {
                                    subcategories = response;
                                }

                                if (subcategories.length > 0) {
                                    subcategories.forEach(function(sub) {
                                        subcategorySelect.append(
                                            `<option value="${sub.id}">${sub.name}</option>`
                                        );
                                    });
                                    subcategorySelect.prop('disabled', false);
                                    console.log('Loaded ' + subcategories.length +
                                        ' subcategories');
                                } else {
                                    subcategorySelect.html(
                                        '<option value="">No subcategories found</option>'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading subcategories:', error);
                                console.error('Response:', xhr.responseText);
                                subcategorySelect.html(
                                    '<option value="">Error loading subcategories</option>'
                                );
                            }
                        });
                    } else {
                        subcategorySelect.html('<option value="">Select Subcategory</option>').prop(
                            'disabled', true);
                    }
                });

                // Subcategory change handler
                $('#addSubcategory').off('change').on('change', function() {
                    const subcategoryId = $(this).val();
                    console.log('Subcategory changed to:', subcategoryId);
                    const modelSelect = $('#addModel');

                    modelSelect.html('<option value="">Loading...</option>').prop('disabled', true);

                    if (subcategoryId) {
                        $.ajax({
                            url: '/inventory/models/' + subcategoryId,
                            method: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                console.log('Models received:', response);
                                modelSelect.html(
                                    '<option value="">Select Model</option>');

                                let models = [];
                                if (response.models) {
                                    models = response.models;
                                } else if (Array.isArray(response)) {
                                    models = response;
                                }

                                if (models.length > 0) {
                                    models.forEach(function(model) {
                                        modelSelect.append(
                                            `<option value="${model.id}">${model.model_name}</option>`
                                        );
                                    });
                                    modelSelect.prop('disabled', false);
                                    console.log('Loaded ' + models.length + ' models');
                                } else {
                                    modelSelect.html(
                                        '<option value="">No models found</option>');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading models:', error);
                                console.error('Response:', xhr.responseText);
                                modelSelect.html(
                                    '<option value="">Error loading models</option>'
                                );
                            }
                        });
                    } else {
                        modelSelect.html('<option value="">Select Model</option>').prop('disabled',
                            true);
                    }
                });
            });

            // Form submission
            $(document).on('submit', '#addInventoryForm', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Adding...');

                $.ajax({
                    url: '/inventory',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message ||
                                    'Inventory added successfully',
                                confirmButtonColor: '#FF9900'
                            }).then(() => {
                                $('#addModal').modal('hide');
                                location.reload();
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

            // Reset form when modal is closed
            $(document).on('hidden.bs.modal', '#addModal', function() {
                $('#addInventoryForm')[0].reset();
                $('#addCategory').val('');
                $('#addSubcategory').html('<option value="">Select Subcategory</option>').prop('disabled',
                    true);
                $('#addModel').html('<option value="">Select Model</option>').prop('disabled', true);
            });
        });
    </script>
@endpush
