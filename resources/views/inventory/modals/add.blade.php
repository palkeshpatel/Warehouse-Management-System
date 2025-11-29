<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Inventory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addInventoryForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        @if (auth()->user()->isSuperAdmin())
                            <div class="col-md-6">
                                <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                                <select name="warehouse_id" class="form-select" id="addWarehouse" required>
                                    <option value="">Select Warehouse</option>
                                    @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" id="addCategory" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subcategory <span class="text-danger">*</span></label>
                            <select name="subcategory_id" class="form-select" id="addSubcategory" required disabled>
                                <option value="">Select Subcategory</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model <span class="text-danger">*</span></label>
                            <select name="model_id" class="form-select" id="addModel" required disabled>
                                <option value="">Select Model</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="qty" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice (Optional)</label>
                            <input type="file" name="invoice" class="form-control" accept=".jpg,.jpeg,.pdf">
                            <small class="text-muted">Max size: 50MB (JPG, JPEG, PDF)</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks (optional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Add Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Category change - load subcategories
        $('#addCategory').on('change', function() {
            const categoryId = $(this).val();
            const subcategorySelect = $('#addSubcategory');
            const modelSelect = $('#addModel');

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

        // Subcategory change - load models
        $('#addSubcategory').on('change', function() {
            const subcategoryId = $(this).val();
            const modelSelect = $('#addModel');

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

        // Form submission
        $('#addInventoryForm').on('submit', function(e) {
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
        $('#addModal').on('hidden.bs.modal', function() {
            $('#addInventoryForm')[0].reset();
            $('#addSubcategory, #addModel').html('<option value="">Select...</option>').prop('disabled',
                true);
        });
    });
</script>
