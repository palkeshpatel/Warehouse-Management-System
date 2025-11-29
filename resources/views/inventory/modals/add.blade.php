<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addInventoryForm">
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
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory_id" id="subcategorySelect" class="form-select" required disabled>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <select name="model_id" id="modelSelect" class="form-select" required disabled>
                            <option value="">Select Model</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="qty" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Invoice (Optional)</label>
                        <input type="file" name="invoice" class="form-control" accept=".jpg,.jpeg,.pdf">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#categorySelect').on('change', function() {
        const categoryId = $(this).val();
        if (categoryId) {
            $.get('/inventory/subcategories/' + categoryId, function(data) {
                $('#subcategorySelect').empty().append('<option value="">Select Subcategory</option>');
                data.forEach(function(item) {
                    $('#subcategorySelect').append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                $('#subcategorySelect').prop('disabled', false);
            });
        }
    });

    $('#subcategorySelect').on('change', function() {
        const subcategoryId = $(this).val();
        if (subcategoryId) {
            $.get('/inventory/models/' + subcategoryId, function(data) {
                $('#modelSelect').empty().append('<option value="">Select Model</option>');
                data.forEach(function(item) {
                    $('#modelSelect').append('<option value="' + item.id + '">' + item.model_name + '</option>');
                });
                $('#modelSelect').prop('disabled', false);
            });
        }
    });

    $('#addInventoryForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: '/inventory',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    $('#addModal').modal('hide');
                    location.reload();
                }
            },
            error: handleAjaxError
        });
    });
});
</script>
@endpush

