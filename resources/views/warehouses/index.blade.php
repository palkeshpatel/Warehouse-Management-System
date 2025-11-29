@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Warehouse Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#warehouseModal" onclick="resetForm()">Add Warehouse</button>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-building me-2"></i>Warehouses List</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($warehouses as $warehouse)
                <tr>
                    <td>{{ $warehouse->name }}</td>
                    <td>{{ $warehouse->location }}</td>
                    <td>{{ $warehouse->address }}</td>
                    <td>{{ $warehouse->contact_number }}</td>
                    <td>{{ $warehouse->email }}</td>
                    <td>
                        <span class="badge bg-{{ $warehouse->status === 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($warehouse->status) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editWarehouse({{ $warehouse->id }})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteWarehouse({{ $warehouse->id }})">Delete</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Warehouse Modal -->
<div class="modal fade" id="warehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Warehouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="warehouseForm">
                <input type="hidden" id="warehouseId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location *</label>
                        <input type="text" name="location" id="location" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetForm() {
    $('#warehouseForm')[0].reset();
    $('#warehouseId').val('');
    $('#modalTitle').text('Add Warehouse');
}

function editWarehouse(id) {
    $.get('/warehouses/' + id, function(data) {
        $('#warehouseId').val(data.id);
        $('#name').val(data.name);
        $('#location').val(data.location);
        $('#address').val(data.address);
        $('#contact_number').val(data.contact_number);
        $('#email').val(data.email);
        $('#status').val(data.status);
        $('#modalTitle').text('Edit Warehouse');
        $('#warehouseModal').modal('show');
    });
}

function deleteWarehouse(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/warehouses/' + id,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        location.reload();
                    }
                },
                error: handleAjaxError
            });
        }
    });
}

$('#warehouseForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const id = $('#warehouseId').val();
    const url = id ? '/warehouses/' + id : '/warehouses';
    const method = id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData,
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                $('#warehouseModal').modal('hide');
                location.reload();
            }
        },
        error: handleAjaxError
    });
});
</script>
@endpush

