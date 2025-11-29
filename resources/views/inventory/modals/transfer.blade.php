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
                        <select name="from_warehouse_id" class="form-select" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">To Warehouse</label>
                        <select name="to_warehouse_id" class="form-select" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="transferCategorySelect" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory_id" id="transferSubcategorySelect" class="form-select" required disabled>
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
        if (categoryId) {
            $.get('/inventory/subcategories/' + categoryId, function(data) {
                $('#transferSubcategorySelect').empty().append('<option value="">Select Subcategory</option>');
                data.forEach(function(item) {
                    $('#transferSubcategorySelect').append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                $('#transferSubcategorySelect').prop('disabled', false);
            });
        }
    });

    $('#transferSubcategorySelect').on('change', function() {
        const subcategoryId = $(this).val();
        if (subcategoryId) {
            $.get('/inventory/models/' + subcategoryId, function(data) {
                $('#transferModelSelect').empty().append('<option value="">Select Model</option>');
                data.forEach(function(item) {
                    $('#transferModelSelect').append('<option value="' + item.id + '">' + item.model_name + '</option>');
                });
                $('#transferModelSelect').prop('disabled', false);
            });
        }
    });

    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/inventory/transfer',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    $('#transferModal').modal('hide');
                    location.reload();
                }
            },
            error: handleAjaxError
        });
    });
});
</script>
@endpush

