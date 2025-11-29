@extends('layouts.app')

@section('title', 'Models')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Model Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modelModal" onclick="resetModelForm()">
        <i class="bi bi-plus-circle me-2"></i>Add Model
    </button>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-box me-2"></i>Models List</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Model Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($models as $model)
                <tr>
                    <td>{{ $model->subcategory->category->name }}</td>
                    <td>{{ $model->subcategory->name }}</td>
                    <td>{{ $model->model_name }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editModel({{ $model->id }})">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteModel({{ $model->id }})">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No models found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Model Modal -->
<div class="modal fade" id="modelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modelModalTitle">Add Model</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="modelForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="modelId">
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" id="modelCategory" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory <span class="text-danger">*</span></label>
                        <select name="subcategory_id" class="form-select" id="modelSubcategory" required disabled>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model Name <span class="text-danger">*</span></label>
                        <input type="text" name="model_name" class="form-control" id="modelName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load subcategories when category changes
    $('#modelCategory').on('change', function() {
        const categoryId = $(this).val();
        const subcategorySelect = $('#modelSubcategory');
        
        subcategorySelect.html('<option value="">Loading...</option>').prop('disabled', true);

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
                    console.error('Error loading subcategories:', xhr);
                    subcategorySelect.html('<option value="">Error loading subcategories</option>');
                }
            });
        } else {
            subcategorySelect.html('<option value="">Select Subcategory</option>').prop('disabled', true);
        }
    });
});

function resetModelForm() {
    $('#modelForm')[0].reset();
    $('#modelId').val('');
    $('#modelSubcategory').html('<option value="">Select Subcategory</option>').prop('disabled', true);
    $('#modelModalTitle').text('Add Model');
}

function editModel(id) {
    $.ajax({
        url: '/masters/models/' + id,
        method: 'GET',
        success: function(response) {
            $('#modelId').val(response.id);
            $('#modelCategory').val(response.subcategory.category_id);
            $('#modelCategory').trigger('change');
            
            setTimeout(function() {
                $('#modelSubcategory').val(response.subcategory_id);
            }, 500);
            
            $('#modelName').val(response.model_name);
            $('#modelModalTitle').text('Edit Model');
            $('#modelModal').modal('show');
        },
        error: handleAjaxError
    });
}

function deleteModel(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF9900',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/masters/models/' + id,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            confirmButtonColor: '#FF9900'
                        }).then(() => location.reload());
                    }
                },
                error: handleAjaxError
            });
        }
    });
}

$('#modelForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const modelId = $('#modelId').val();
    const url = modelId ? '/masters/models/' + modelId : '/masters/models';
    const method = modelId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData + (modelId ? '&_method=PUT' : ''),
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    confirmButtonColor: '#FF9900'
                }).then(() => {
                    $('#modelModal').modal('hide');
                    location.reload();
                });
            }
        },
        error: handleAjaxError
    });
});
</script>
@endpush

