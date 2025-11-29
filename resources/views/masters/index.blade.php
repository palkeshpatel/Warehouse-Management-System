@extends('layouts.app')

@section('title', 'Masters')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Masters Management</h2>
            <p class="text-muted mb-0">Manage your Category → Subcategory → Model hierarchy</p>
        </div>
    </div>

    <div class="masters-tree-container">
        @forelse($categories as $category)
            <div class="master-category-card mb-3">
                <!-- Category Level -->
                <div class="category-header" onclick="toggleCategory({{ $category->id }})">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center flex-grow-1">
                            <i class="bi bi-chevron-right me-3 category-chevron" id="cat-chevron-{{ $category->id }}"></i>
                            <i class="bi bi-folder-fill text-warning me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $category->name }}</h5>
                                <small class="text-muted">Category</small>
                            </div>
                            <span class="badge bg-success ms-3">Active</span>
                            <span class="badge bg-secondary ms-2">{{ $category->subcategories->count() }}
                                Subcategories</span>
                        </div>
                        <div class="category-actions" onclick="event.stopPropagation();">
                            <button class="btn btn-sm btn-outline-primary" onclick="editCategory({{ $category->id }})"
                                title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory({{ $category->id }})"
                                title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="openAddSubcategoryModal({{ $category->id }})"
                                title="Add Subcategory">
                                <i class="bi bi-plus-circle me-1"></i>ADD SUB-CATEGORY
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Subcategories Container -->
                <div class="subcategories-container" id="category-{{ $category->id }}" style="display: none;">
                    @if ($category->subcategories->count() > 0)
                        <div class="subcategory-header-text ps-5 py-2">
                            <strong>Sub-Categories ({{ $category->subcategories->count() }}):</strong>
                        </div>
                    @endif

                    @forelse($category->subcategories as $subcategory)
                        <div class="subcategory-wrapper ms-4">
                            <div class="subcategory-card mb-2">
                                <div class="subcategory-header"
                                    onclick="toggleSubcategory({{ $category->id }}, {{ $subcategory->id }})">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <i class="bi bi-chevron-right me-3 subcategory-chevron"
                                                id="sub-chevron-{{ $category->id }}-{{ $subcategory->id }}"></i>
                                            <i class="bi bi-folder2-open text-info me-2" style="font-size: 1.3rem;"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $subcategory->name }}</h6>
                                                <small class="text-muted">Subcategory</small>
                                            </div>
                                            <span class="badge bg-success ms-3">Active</span>
                                            <span
                                                class="badge bg-info ms-2">{{ $subcategory->models ? $subcategory->models->count() : 0 }}
                                                Items</span>
                                        </div>
                                        <div class="subcategory-actions" onclick="event.stopPropagation();">
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="editSubcategory({{ $subcategory->id }})" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="deleteSubcategory({{ $subcategory->id }})" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info"
                                                onclick="openAddModelModal({{ $subcategory->id }}, {{ $category->id }})"
                                                title="Add Model">
                                                <i class="bi bi-plus-circle me-1"></i>ADD ITEM
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Models Container -->
                                <div class="models-container" id="subcategory-{{ $category->id }}-{{ $subcategory->id }}" style="display: none;">
                                    @if ($subcategory->models && $subcategory->models->count() > 0)
                                        <div class="model-header-text ps-5 py-2">
                                            <strong>Items in {{ $subcategory->name }}:</strong>
                                        </div>
                                    @endif

                                    @forelse($subcategory->models as $model)
                                        <div class="model-item ms-4 mb-2">
                                            <div class="model-card">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center flex-grow-1">
                                                        <i class="bi bi-box-seam text-success me-2"
                                                            style="font-size: 1.2rem;"></i>
                                                        <div>
                                                            <strong>{{ $model->model_name }}</strong>
                                                            <small class="text-muted d-block">Model</small>
                                                        </div>
                                                        <span class="badge bg-success ms-3">Active</span>
                                                    </div>
                                                    <div class="model-actions">
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            onclick="editModel({{ $model->id }})" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteModel({{ $model->id }})" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="empty-message ms-4 py-2 text-muted">
                                            <i class="bi bi-info-circle me-2"></i>No models found. Click "ADD ITEM" to add a
                                            model.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-message ps-5 py-3 text-muted">
                            <i class="bi bi-info-circle me-2"></i>No subcategories found. Click "ADD SUB-CATEGORY" to add a
                            subcategory.
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-muted">No categories found</h5>
                    <p class="text-muted">Start by adding your first category!</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#categoryModal"
                        onclick="resetCategoryForm()">
                        <i class="bi bi-plus-circle me-2"></i>Add Category
                    </button>
                </div>
            </div>
        @endforelse

        <!-- Add Category Button at Top Right -->
        @if ($categories->count() > 0)
            <div class="text-end mt-4 mb-3">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#categoryModal"
                    onclick="resetCategoryForm()">
                    <i class="bi bi-plus-circle me-2"></i>ADD CATEGORY
                </button>
            </div>
        @endif
    </div>

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="categoryModalTitle"><i class="bi bi-folder me-2"></i>Add Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="categoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="categoryId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg" id="categoryName"
                                placeholder="Enter category name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i
                                class="bi bi-check-circle me-2"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Subcategory Modal -->
    <div class="modal fade" id="subcategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="subcategoryModalTitle"><i class="bi bi-folder2 me-2"></i>Add Subcategory
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="subcategoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="subcategoryId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select form-select-lg" id="subcategoryCategory"
                                required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Subcategory Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg"
                                id="subcategoryName" placeholder="Enter subcategory name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i
                                class="bi bi-check-circle me-2"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Model Modal -->
    <div class="modal fade" id="modelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modelModalTitle"><i class="bi bi-box me-2"></i>Add Model</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="modelForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="modelId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select form-select-lg" id="modelCategory" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Subcategory <span class="text-danger">*</span></label>
                            <select name="subcategory_id" class="form-select form-select-lg" id="modelSubcategory"
                                required disabled>
                                <option value="">Select Subcategory</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Model Name <span class="text-danger">*</span></label>
                            <input type="text" name="model_name" class="form-control form-control-lg" id="modelName"
                                placeholder="Enter model name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info"><i class="bi bi-check-circle me-2"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .masters-tree-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .master-category-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .master-category-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .category-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            padding: 1.25rem 1.5rem;
            cursor: pointer;
            border-bottom: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .category-header:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
        }

        .category-chevron,
        .subcategory-chevron {
            transition: transform 0.3s ease;
            font-size: 1.1rem;
            color: #6c757d;
        }

        .bi-chevron-right {
            transform: rotate(0deg);
        }

        .bi-chevron-down {
            transform: rotate(0deg);
        }

        .bi-chevron-right.rotated,
        .bi-chevron-down.rotated {
            transform: rotate(90deg);
        }

        .category-actions,
        .subcategory-actions,
        .model-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .subcategories-container {
            background: #fafafa;
            border-top: 2px dotted #dee2e6;
            padding: 1rem 0;
        }

        .subcategory-wrapper {
            position: relative;
            padding-left: 2rem;
            border-left: 3px solid #0dcaf0;
            margin-left: 2rem;
            margin-bottom: 0.5rem;
        }

        .subcategory-card {
            background: #ffffff;
            border: 1px solid #d1ecf1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .subcategory-header {
            background: #ffffff;
            padding: 1rem 1.25rem;
            cursor: pointer;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .subcategory-header:hover {
            background: #f8f9fa;
        }

        .subcategory-header-text,
        .model-header-text {
            color: #6c757d;
            font-size: 0.9rem;
            border-bottom: 1px dotted #dee2e6;
            margin-bottom: 0.5rem;
        }

        .models-container {
            background: #f8f9fa;
            border-top: 2px dotted #d1ecf1;
            padding: 0.75rem 0;
        }

        .model-item {
            position: relative;
            padding-left: 1.5rem;
            border-left: 3px solid #198754;
            margin-left: 1.5rem;
        }

        .model-card {
            background: #ffffff;
            border: 1px solid #d1f2eb;
            border-radius: 6px;
            padding: 0.875rem 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .model-card:hover {
            background: #f0fdf4;
            border-color: #198754;
            transform: translateX(3px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .empty-message {
            font-style: italic;
            color: #adb5bd;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            font-weight: 500;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-outline-primary,
        .btn-outline-danger {
            border-width: 1.5px;
        }

        .btn-outline-primary:hover {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        @media (max-width: 768px) {

            .category-actions,
            .subcategory-actions {
                flex-wrap: wrap;
            }

            .category-actions .btn,
            .subcategory-actions .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Toggle Functions
        function toggleCategory(categoryId) {
            const container = document.getElementById('category-' + categoryId);
            const chevron = document.getElementById('cat-chevron-' + categoryId);

            if (container.style.display === 'none' || !container.style.display) {
                container.style.display = 'block';
                chevron.classList.remove('bi-chevron-right');
                chevron.classList.add('bi-chevron-down');
                chevron.classList.add('rotated');
            } else {
                container.style.display = 'none';
                chevron.classList.remove('bi-chevron-down', 'rotated');
                chevron.classList.add('bi-chevron-right');
            }
        }

        function toggleSubcategory(categoryId, subcategoryId) {
            const container = document.getElementById('subcategory-' + categoryId + '-' + subcategoryId);
            const chevron = document.getElementById('sub-chevron-' + categoryId + '-' + subcategoryId);

            if (container.style.display === 'none' || !container.style.display) {
                container.style.display = 'block';
                chevron.classList.remove('bi-chevron-right');
                chevron.classList.add('bi-chevron-down');
                chevron.classList.add('rotated');
            } else {
                container.style.display = 'none';
                chevron.classList.remove('bi-chevron-down', 'rotated');
                chevron.classList.add('bi-chevron-right');
            }
        }

        // Quick Add Functions
        function openAddSubcategoryModal(categoryId) {
            resetSubcategoryForm();
            $('#subcategoryCategory').val(categoryId);
            $('#subcategoryModal').modal('show');
        }

        function openAddModelModal(subcategoryId, categoryId) {
            resetModelForm();
            $('#modelCategory').val(categoryId);
            $('#modelCategory').trigger('change');
            setTimeout(function() {
                $('#modelSubcategory').val(subcategoryId);
            }, 500);
            $('#modelModal').modal('show');
        }

        // Category Functions
        function resetCategoryForm() {
            $('#categoryForm')[0].reset();
            $('#categoryId').val('');
            $('#categoryModalTitle').html('<i class="bi bi-folder me-2"></i>Add Category');
        }

        function editCategory(id) {
            $.ajax({
                url: '/masters/categories/' + id,
                method: 'GET',
                success: function(response) {
                    $('#categoryId').val(response.id);
                    $('#categoryName').val(response.name);
                    $('#categoryModalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Category');
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

        // Subcategory Functions
        function resetSubcategoryForm() {
            $('#subcategoryForm')[0].reset();
            $('#subcategoryId').val('');
            $('#subcategoryModalTitle').html('<i class="bi bi-folder2 me-2"></i>Add Subcategory');
        }

        function editSubcategory(id) {
            $.ajax({
                url: '/masters/subcategories/' + id,
                method: 'GET',
                success: function(response) {
                    $('#subcategoryId').val(response.id);
                    $('#subcategoryCategory').val(response.category_id);
                    $('#subcategoryName').val(response.name);
                    $('#subcategoryModalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Subcategory');
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

        // Model Functions
        $(document).ready(function() {
            $('#modelCategory').on('change', function() {
                const categoryId = $(this).val();
                const subcategorySelect = $('#modelSubcategory');

                subcategorySelect.html('<option value="">Loading...</option>').prop('disabled', true);

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
        });

        function resetModelForm() {
            $('#modelForm')[0].reset();
            $('#modelId').val('');
            $('#modelSubcategory').html('<option value="">Select Subcategory</option>').prop('disabled', true);
            $('#modelModalTitle').html('<i class="bi bi-box me-2"></i>Add Model');
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
                    $('#modelModalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Model');
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

        // Form Submissions
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
