@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>User Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()">Add User</button>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Users List</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Warehouse</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-info">{{ $user->role->name ?? 'N/A' }}</span>
                    </td>
                    <td>{{ $user->warehouse->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editUser({{ $user->id }})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUser({{ $user->id }})">Delete</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span id="passwordLabel">*</span></label>
                        <input type="password" name="password" id="password" class="form-control">
                        <small class="text-muted" id="passwordHelp">Leave blank to keep current password when editing</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role_id" id="role_id" class="form-select" required>
                            <option value="2">Admin</option>
                            <option value="3">Employee</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warehouse *</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
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
    $('#userForm')[0].reset();
    $('#userId').val('');
    $('#modalTitle').text('Add User');
    $('#password').prop('required', true);
    $('#passwordLabel').text('*');
    $('#passwordHelp').hide();
}

function editUser(id) {
    $.get('/users/' + id, function(data) {
        $('#userId').val(data.id);
        $('#name').val(data.name);
        $('#email').val(data.email);
        $('#role_id').val(data.role_id);
        $('#warehouse_id').val(data.warehouse_id);
        $('#status').val(data.status);
        $('#password').prop('required', false);
        $('#passwordLabel').text('');
        $('#passwordHelp').show();
        $('#modalTitle').text('Edit User');
        $('#userModal').modal('show');
    });
}

function deleteUser(id) {
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
                url: '/users/' + id,
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

$('#userForm').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const id = $('#userId').val();
    const url = id ? '/users/' + id : '/users';
    const method = id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData,
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                $('#userModal').modal('hide');
                location.reload();
            }
        },
        error: handleAjaxError
    });
});
</script>
@endpush

