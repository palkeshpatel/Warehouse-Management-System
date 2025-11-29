@extends('layouts.app')

@section('title', 'Subcategories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Subcategory Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subcategoryModal" onclick="resetSubcategoryForm()">
        <i class="bi bi-plus-circle me-2"></i>Add Subcategory
    </button>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-folder2 me-2"></i>Subcategories List</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Models</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subcategories as $subcategory)
                <tr>
                    <td>{{ $subcategory->category->name }}</td>
                    <td>{{ $subcategory->name }}</td>
                    <td>
                        <span class="badge bg-info">{{ $subcategory->models->count() ?? 0 }}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editSubcategory({{ $subcategory->id }})">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSubcategory({{ $subcategory->id }})">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No subcategories found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Subcategory Modal -->
<div class="modal fade" id="subcategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="subcategoryModalTitle">Add Subcategory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="subcategoryForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="subcategoryId">
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" id="subcategoryCategory" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" id="subcategoryName" required>
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
function resetSubcategoryForm() {
    $('#subcategoryForm')[0].reset();
    $('#subcategoryId').val('');
    $('#subcategoryModalTitle').text('Add Subcategory');
}

function editSubcategory(id) {
    $.ajax({
        url: '/masters/subcategories/' + id,
        method: 'GET',
        success: function(response) {
            $('#subcategoryId').val(response.id);
            $('#subcategoryCategory').val(response.category_id);
            $('#subcategoryName').val(response.name);
            $('#subcategoryModalTitle').text('Edit Subcategory');
            $('#subcategoryModal').modal('show');
        },
        error: handleAjaxError
    });
}

function deleteSubcategory(id) {
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
                url: '/masters/subcategories/' + id,
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

$('#subcategoryForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const subcategoryId = $('#subcategoryId').val();
    const url = subcategoryId ? '/masters/subcategories/' + subcategoryId : '/masters/subcategories';
    const method = subcategoryId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData + (subcategoryId ? '&_method=PUT' : ''),
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    confirmButtonColor: '#FF9900'
                }).then(() => {
                    $('#subcategoryModal').modal('hide');
                    location.reload();
                });
            }
        },
        error: handleAjaxError
    });
});
</script>
@endpush

