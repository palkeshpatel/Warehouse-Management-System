@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventory Management</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">Add Inventory</button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deductModal">Deduct Inventory</button>
            @if (auth()->user()->isSuperAdmin())
                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#transferModal">Transfer Stock</button>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Inventory List</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Warehouse</th>
                        <th>Total Stock</th>
                        <th>Available</th>
                        @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $item)
                        <tr>
                            <td>{{ $item->model->model_name ?? 'N/A' }}</td>
                            <td>{{ $item->model->subcategory->category->name ?? 'N/A' }}</td>
                            <td>{{ $item->model->subcategory->name ?? 'N/A' }}</td>
                            <td>{{ $item->warehouse->name ?? 'N/A' }}</td>
                            <td>{{ $item->total_stock }}</td>
                            <td>{{ $item->available_stock }}</td>
                            @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() ? '7' : '6' }}"
                                class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No inventory found. Click "Add Inventory" to add items.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $inventory->links() }}
        </div>
    </div>

    @include('inventory.modals.add')
    @include('inventory.modals.deduct')
    @if (auth()->user()->isSuperAdmin())
        @include('inventory.modals.transfer')
    @endif
@endsection
