@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Category Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetCategoryForm()">
            <i class="bi bi-plus-circle me-2"></i>Add Category
        </button>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-folder me-2"></i>Categories List</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Subcategories</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>
                                <span class="badge bg-info">{{ $category->subcategories->count() }}</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editCategory({{ $category->id }})">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory({{ $category->id }})">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No categories found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="categoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="categoryId">
                        <div class="mb-3">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" id="categoryName" required>
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
        function resetCategoryForm() {
            $('#categoryForm')[0].reset();
            $('#categoryId').val('');
            $('#categoryModalTitle').text('Add Category');
        }

        function editCategory(id) {
            $.ajax({
                url: '/masters/categories/' + id,
                method: 'GET',
                success: function(response) {
                    $('#categoryId').val(response.id);
                    $('#categoryName').val(response.name);
                    $('#categoryModalTitle').text('Edit Category');
                    $('#categoryModal').modal('show');
                },
                error: handleAjaxError
            });
        }

        function deleteCategory(id) {
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
                        url: '/masters/categories/' + id,
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

        $('#categoryForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const categoryId = $('#categoryId').val();
            const url = categoryId ? '/masters/categories/' + categoryId : '/masters/categories';
            const method = categoryId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData + (categoryId ? '&_method=PUT' : ''),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            confirmButtonColor: '#FF9900'
                        }).then(() => {
                            $('#categoryModal').modal('hide');
                            location.reload();
                        });
                    }
                },
                error: handleAjaxError
            });
        });
    </script>
@endpush
