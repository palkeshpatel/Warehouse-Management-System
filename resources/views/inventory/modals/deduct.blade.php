<div class="modal fade" id="deductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deduct Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deductInventoryForm">
                <div class="modal-body">
                    @if(auth()->user()->isSuperAdmin())
                        <div class="mb-3">
                            <label class="form-label">Warehouse</label>
                            <select name="warehouse_id" class="form-select" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="deductCategorySelect" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory_id" id="deductSubcategorySelect" class="form-select" required disabled>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <select name="model_id" id="deductModelSelect" class="form-select" required disabled>
                            <option value="">Select Model</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="qty" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Invoice <span class="text-danger">*</span></label>
                        <input type="file" name="invoice" class="form-control" accept=".jpg,.jpeg,.pdf" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Deduct Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#deductCategorySelect').on('change', function() {
        const categoryId = $(this).val();
        if (categoryId) {
            $.get('/inventory/subcategories/' + categoryId, function(data) {
                $('#deductSubcategorySelect').empty().append('<option value="">Select Subcategory</option>');
                data.forEach(function(item) {
                    $('#deductSubcategorySelect').append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                $('#deductSubcategorySelect').prop('disabled', false);
            });
        }
    });

    $('#deductSubcategorySelect').on('change', function() {
        const subcategoryId = $(this).val();
        if (subcategoryId) {
            $.get('/inventory/models/' + subcategoryId, function(data) {
                $('#deductModelSelect').empty().append('<option value="">Select Model</option>');
                data.forEach(function(item) {
                    $('#deductModelSelect').append('<option value="' + item.id + '">' + item.model_name + '</option>');
                });
                $('#deductModelSelect').prop('disabled', false);
            });
        }
    });

    $('#deductInventoryForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: '/inventory/deduct',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    $('#deductModal').modal('hide');
                    location.reload();
                }
            },
            error: handleAjaxError
        });
    });
});
</script>
@endpush

