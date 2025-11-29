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

    @if (isset($groupedInventory) && count($groupedInventory) > 0)
        @foreach ($groupedInventory as $warehouseData)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>{{ $warehouseData['warehouse']->name }}
                        <small class="ms-2">({{ $warehouseData['warehouse']->location }})</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach ($warehouseData['categories'] as $category)
                            @foreach ($category['subcategories'] as $subcategory)
                                <div class="col-md-6 col-lg-4">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="text-info mb-3">
                                            <i class="bi bi-folder2-open me-2"></i>{{ $subcategory['name'] }} -
                                            {{ $category['name'] }}
                                            <small class="text-muted">
                                                (Available: {{ number_format($subcategory['available_stock']) }})
                                            </small>
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Model</th>
                                                        <th class="text-end">Available</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($subcategory['models'] ?? [] as $model)
                                                        <tr>
                                                            <td>
                                                                <i class="bi bi-box-seam text-success me-2"></i>
                                                                <small>{{ $model['name'] }}</small>
                                                            </td>
                                                            <td class="text-end">
                                                                <span
                                                                    class="badge bg-success">{{ number_format($model['available_stock']) }}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    @if (empty($subcategory['models'] ?? []))
                                                        <tr>
                                                            <td colspan="2" class="text-center text-muted">
                                                                <small>No models found</small>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <p>No inventory found. Click "Add Inventory" to add items.</p>
            </div>
        </div>
    @endif

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

            // ========== DEDUCT INVENTORY MODAL ==========
            // Bind events when modal is shown (after it's fully displayed)
            $(document).on('shown.bs.modal', '#deductModal', function() {
                console.log('Deduct Modal is now shown');

                // Category change handler
                $(document).off('change', '#deductCategory').on('change', '#deductCategory', function() {
                    const categoryId = $(this).val();
                    console.log('Category changed to:', categoryId);

                    const subcategorySelect = $('#deductSubcategory');
                    const modelSelect = $('#deductModel');

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

                                let subcategories = response.subcategories || response;

                                if (Array.isArray(subcategories) && subcategories
                                    .length > 0) {
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
                $(document).off('change', '#deductSubcategory').on('change', '#deductSubcategory',
                    function() {
                        const subcategoryId = $(this).val();
                        console.log('Subcategory changed to:', subcategoryId);
                        const modelSelect = $('#deductModel');

                        modelSelect.html('<option value="">Loading...</option>').prop('disabled', true);
                        $('#deductAvailableStock').val('').prop('disabled', true);
                        $('#deductQty').val('').attr('max', 0).prop('disabled', true);
                        $('#deductQtyHelp').text('Please select a model first').removeClass(
                            'text-danger text-success');

                        if (subcategoryId) {
                            $.ajax({
                                url: '/inventory/models/' + subcategoryId,
                                method: 'GET',
                                dataType: 'json',
                                success: function(response) {
                                    console.log('Models received:', response);
                                    modelSelect.html(
                                        '<option value="">Select Model</option>');

                                    let models = response.models || response;

                                    if (Array.isArray(models) && models.length > 0) {
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

                // Model change handler - load available stock
                $(document).off('change', '#deductModel').on('change', '#deductModel', function() {
                    const modelId = $(this).val();
                    const warehouseId = $('#deductWarehouse').length > 0 && $('#deductWarehouse')
                        .val() ? $('#deductWarehouse').val() :
                        '{{ auth()->user()->warehouse_id ?? '' }}';

                    if (!modelId) {
                        $('#deductAvailableStock').val('').prop('disabled', true);
                        $('#deductQty').val('').attr('max', 0).prop('disabled', true);
                        $('#deductQtyHelp').text('Please select a model first').removeClass(
                            'text-danger text-success');
                        return;
                    }

                    // For non-super admin, warehouse_id is already set, for super admin check if warehouse is selected
                    if ($('#deductWarehouse').length > 0 && !warehouseId) {
                        $('#deductAvailableStock').val('0').prop('disabled', false);
                        $('#deductQty').val('').attr('max', 0).prop('disabled', true);
                        $('#deductQtyHelp').text('Please select a warehouse first').addClass(
                            'text-danger').removeClass('text-success');
                        return;
                    }

                    // Fetch available stock
                    $.ajax({
                        url: '/inventory/available-stock',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            model_id: modelId,
                            warehouse_id: warehouseId
                        },
                        success: function(response) {
                            const availableStock = response.available_stock || 0;
                            const totalStock = response.total_stock || 0;

                            $('#deductAvailableStock').val(availableStock
                                .toLocaleString()).prop('disabled', false);
                            $('#deductQty').attr('max', availableStock);

                            if (availableStock > 0) {
                                $('#deductQty').prop('disabled', false);
                                $('#deductQtyHelp').text(
                                    `You can deduct up to ${availableStock.toLocaleString()} units`
                                ).removeClass('text-danger').addClass(
                                    'text-success');
                            } else {
                                $('#deductQty').prop('disabled', true);
                                $('#deductQtyHelp').text(
                                    'No stock available for this model in this warehouse'
                                ).addClass('text-danger').removeClass(
                                    'text-success');
                            }

                            console.log('Available stock:', availableStock);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading available stock:', error);
                            $('#deductAvailableStock').val('0').prop('disabled', false);
                            $('#deductQty').val('').attr('max', 0).prop('disabled',
                                true);
                            $('#deductQtyHelp').text('Error loading stock information')
                                .addClass('text-danger').removeClass('text-success');
                        }
                    });
                });

                // Warehouse change handler (for Super Admin) - reload stock if model is selected
                $(document).off('change', '#deductWarehouse').on('change', '#deductWarehouse', function() {
                    const modelId = $('#deductModel').val();
                    if (modelId) {
                        $('#deductModel').trigger('change');
                    }
                });

                // Quantity input validation
                $(document).off('input change', '#deductQty').on('input change', '#deductQty', function() {
                    const qty = parseInt($(this).val()) || 0;
                    const maxQty = parseInt($(this).attr('max')) || 0;

                    if (qty > maxQty && maxQty > 0) {
                        $(this).val(maxQty);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Quantity Exceeded',
                            text: `Maximum available stock is ${maxQty.toLocaleString()} units`,
                            confirmButtonColor: '#FF9900',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                });
            });

            // Deduct form submission
            $(document).on('submit', '#deductInventoryForm', function(e) {
                e.preventDefault();

                const qty = parseInt($('#deductQty').val()) || 0;
                const maxQty = parseInt($('#deductQty').attr('max')) || 0;
                const availableStock = parseInt($('#deductAvailableStock').val().replace(/,/g, '')) || 0;

                // Final validation before submission
                if (qty <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Quantity',
                        text: 'Please enter a valid quantity',
                        confirmButtonColor: '#FF9900'
                    });
                    return;
                }

                if (qty > maxQty || qty > availableStock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Quantity Exceeded',
                        text: `You cannot deduct more than ${availableStock.toLocaleString()} units. Available stock: ${availableStock.toLocaleString()}`,
                        confirmButtonColor: '#FF9900'
                    });
                    return;
                }

                const formData = new FormData(this);
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Deducting...');

                $.ajax({
                    url: '/inventory/deduct',
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
                                text: response.message ||
                                    'Inventory deducted successfully',
                                confirmButtonColor: '#FF9900'
                            }).then(() => {
                                $('#deductModal').modal('hide');
                                // Force reload to show updated stock values
                                window.location.reload(true);
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

            // Reset deduct form when modal is closed
            $(document).on('hidden.bs.modal', '#deductModal', function() {
                $('#deductInventoryForm')[0].reset();
                $('#deductCategory').val('');
                $('#deductSubcategory').html('<option value="">Select Subcategory</option>').prop(
                    'disabled', true);
                $('#deductModel').html('<option value="">Select Model</option>').prop('disabled', true);
                $('#deductAvailableStock').val('').prop('disabled', true);
                $('#deductQty').val('').attr('max', 0).prop('disabled', true);
                $('#deductQtyHelp').text('Please select a model first').removeClass(
                    'text-danger text-success');
                @if (auth()->user()->isSuperAdmin())
                    $('#deductWarehouse').val('');
                @endif
            });
        });
    </script>
@endpush
