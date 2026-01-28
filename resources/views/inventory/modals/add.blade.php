<!-- First Modal: Transaction Type Selection -->
<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Inventory - Select Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-4">Please select the transaction type:</p>
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-primary btn-lg px-5" id="btnSelectPurchaseStock">
                        <i class="bi bi-cart-plus me-2"></i>Purchase Stock
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-lg px-5" id="btnSelectSalesReturn">
                        <i class="bi bi-arrow-return-left me-2"></i>Return (Sales)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Modal: Actual Add Inventory Form -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Inventory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addInventoryForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Transaction Type Display -->
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Transaction Type:</strong> <span id="displayTransactionType"></span>
                    </div>
                    <input type="hidden" name="transaction_subtype" id="addTransactionSubtype" required>

                    <!-- Reason Section (for Sales Return) -->
                    <div class="row mb-3" id="salesReturnReasonSection" style="display: none;">
                        <div class="col-12">
                            <label class="form-label">Return Reason <span class="text-danger">*</span></label>
                            <select name="sales_return_reason_id" class="form-select" id="addSalesReturnReason">
                                <option value="">Select Reason</option>
                                @foreach(\App\Models\SalesReturnReason::where('is_active', true)->get() as $reason)
                                    <option value="{{ $reason->id }}" data-is-other="{{ strtolower($reason->name) === 'other' ? '1' : '0' }}">
                                        {{ $reason->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-2" id="addSalesReturnOtherSection" style="display: none;">
                            <label class="form-label">Specify Other Reason <span class="text-danger">*</span></label>
                            <textarea name="reason_other" class="form-control" rows="2" placeholder="Please specify the reason"></textarea>
                        </div>
                    </div>

                    <div class="row g-3">
                        @if (auth()->user()->isSuperAdmin())
                            <div class="col-md-6">
                                <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                                <select name="warehouse_id" class="form-select" id="addWarehouse" required>
                                    <option value="">Select Warehouse</option>
                                    @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" id="addCategory" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subcategory <span class="text-danger">*</span></label>
                            <select name="subcategory_id" class="form-select" id="addSubcategory" required disabled>
                                <option value="">Select Subcategory</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model <span class="text-danger">*</span></label>
                            <select name="model_id" class="form-select" id="addModel" required disabled>
                                <option value="">Select Model</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="qty" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice (Optional)</label>
                            <input type="file" name="invoice" class="form-control" accept=".jpg,.jpeg,.pdf">
                            <small class="text-muted">Max size: 50MB (JPG, JPEG, PDF)</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks (optional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Add Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
