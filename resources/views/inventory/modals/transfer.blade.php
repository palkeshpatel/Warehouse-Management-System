<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transfer Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">From Warehouse</label>
                        <select name="from_warehouse_id" class="form-select" id="transferFromWarehouse" required>
                            <option value="">Select Warehouse</option>
                            @if(auth()->user()->isSuperAdmin())
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            @else
                                <option value="{{ auth()->user()->warehouse_id }}" selected>{{ auth()->user()->warehouse->name ?? 'Your Warehouse' }}</option>
                            @endif
                        </select>
                        @if(!auth()->user()->isSuperAdmin())
                            <small class="text-muted">You can only transfer from your assigned warehouse</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">To Warehouse</label>
                        <select name="to_warehouse_id" class="form-select" id="transferToWarehouse" required>
                            <option value="">Select Warehouse</option>
                            @foreach ($warehouses as $warehouse)
                                @if(!auth()->user()->isSuperAdmin() && $warehouse->id == auth()->user()->warehouse_id)
                                    {{-- Skip own warehouse in "To Warehouse" for Admin/Employee --}}
                                @else
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="transferCategorySelect" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory_id" id="transferSubcategorySelect" class="form-select" required
                            disabled>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <select name="model_id" id="transferModelSelect" class="form-select" required disabled>
                            <option value="">Select Model</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="qty" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#transferCategorySelect').on('change', function() {
                const categoryId = $(this).val();
                const subcategorySelect = $('#transferSubcategorySelect');
                const modelSelect = $('#transferModelSelect');

                subcategorySelect.html('<option value="">Loading...</option>').prop('disabled', true);
                modelSelect.html('<option value="">Select Model</option>').prop('disabled', true);

                if (categoryId) {
                    $.ajax({
                        url: '/inventory/subcategories/' + categoryId,
                        method: 'GET',
                        success: function(response) {
                            subcategorySelect.html(
                                '<option value="">Select Subcategory</option>');
                            const subcategories = response.subcategories || response;
                            if (Array.isArray(subcategories) && subcategories.length > 0) {
                                subcategories.forEach(function(sub) {
                                    subcategorySelect.append(
                                        `<option value="${sub.id}">${sub.name}</option>`
                                        );
                                });
                                subcategorySelect.prop('disabled', false);
                            } else {
                                subcategorySelect.html(
                                    '<option value="">No subcategories found</option>');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error loading subcategories:', xhr);
                            subcategorySelect.html(
                                '<option value="">Error loading subcategories</option>');
                        }
                    });
                } else {
                    subcategorySelect.html('<option value="">Select Subcategory</option>').prop('disabled',
                        true);
                }
            });

            $('#transferSubcategorySelect').on('change', function() {
                const subcategoryId = $(this).val();
                const modelSelect = $('#transferModelSelect');

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
                                    modelSelect.append(
                                        `<option value="${model.id}">${model.model_name}</option>`
                                        );
                                });
                                modelSelect.prop('disabled', false);
                            } else {
                                modelSelect.html('<option value="">No models found</option>');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error loading models:', xhr);
                            modelSelect.html('<option value="">Error loading models</option>');
                        }
                    });
                } else {
                    modelSelect.html('<option value="">Select Model</option>').prop('disabled', true);
                }
            });

            // Update "To Warehouse" dropdown when "From Warehouse" changes (for Super Admin)
            @if(auth()->user()->isSuperAdmin())
            $('#transferFromWarehouse').on('change', function() {
                const fromWarehouseId = $(this).val();
                const toWarehouseSelect = $('#transferToWarehouse');
                const allWarehouses = @json($warehouses);
                
                toWarehouseSelect.html('<option value="">Select Warehouse</option>');
                allWarehouses.forEach(function(warehouse) {
                    if (warehouse.id != fromWarehouseId) {
                        toWarehouseSelect.append(`<option value="${warehouse.id}">${warehouse.name}</option>`);
                    }
                });
            });
            @endif

            $('#transferForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Transferring...');
                
                $.ajax({
                    url: '/inventory/transfer',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message || 'Stock transferred successfully',
                                confirmButtonColor: '#FF9900'
                            }).then(() => {
                                $('#transferModal').modal('hide');
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
        });
    </script>
@endpush
