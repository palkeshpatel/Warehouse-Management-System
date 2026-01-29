<!-- First Modal: Transaction Type Selection -->
<div class="modal fade" id="deductTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-dash-circle me-2"></i>Deduct Inventory - Select Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-4">Please select the transaction type:</p>
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-danger btn-lg px-5" id="btnSelectSales">
                        <i class="bi bi-cart-dash me-2"></i>Sales
                    </button>
                    <button type="button" class="btn btn-danger btn-lg px-5" id="btnSelectPurchaseReturn">
                        <i class="bi bi-arrow-return-right me-2"></i>Return (Purchase)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Modal: Actual Deduct Inventory Form -->
<div class="modal fade" id="deductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-dash-circle me-2"></i>Deduct Inventory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deductInventoryForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Transaction Type Display -->
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Transaction Type:</strong> <span id="displayDeductTransactionType"></span>
                    </div>
                    <input type="hidden" name="transaction_subtype" id="deductTransactionSubtype" required>

                    <!-- Reason Section (for Purchase Return) -->
                    <div class="row mb-3" id="purchaseReturnReasonSection" style="display: none;">
                        <div class="col-12">
                            <label class="form-label">Return Reason <span class="text-danger">*</span></label>
                            <select name="purchase_return_reason_id" class="form-select" id="deductPurchaseReturnReason">
                                <option value="">Select Reason</option>
                                @foreach(\App\Models\PurchaseReturnReason::where('is_active', true)->get() as $reason)
                                    <option value="{{ $reason->id }}" data-is-other="{{ strtolower($reason->name) === 'other' ? '1' : '0' }}">
                                        {{ $reason->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-2" id="deductPurchaseReturnOtherSection" style="display: none;">
                            <label class="form-label">Specify Other Reason <span class="text-danger">*</span></label>
                            <textarea name="reason_other" class="form-control" rows="2" placeholder="Please specify the reason"></textarea>
                        </div>
                    </div>

                    <div class="row g-3">
                        @if (auth()->user()->isSuperAdmin())
                            <div class="col-md-6">
                                <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                                <select name="warehouse_id" class="form-select" id="deductWarehouse" required>
                                    <option value="">Select Warehouse</option>
                                    @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" id="deductCategory" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subcategory <span class="text-danger">*</span></label>
                            <select name="subcategory_id" class="form-select" id="deductSubcategory" required disabled>
                                <option value="">Select Subcategory</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model <span class="text-danger">*</span></label>
                            <select name="model_id" class="form-select" id="deductModel" required disabled>
                                <option value="">Select Model</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Available Stock</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="deductAvailableStock" readonly style="background-color: #f8f9fa; font-weight: bold;">
                                <span class="input-group-text bg-success text-white">
                                    <i class="bi bi-box-seam me-1"></i>Available
                                </span>
                            </div>
                            <small class="text-muted">Stock available for deduction</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="qty" class="form-control" id="deductQty" min="1" max="0" required disabled>
                            <small class="form-text" id="deductQtyHelp">Please select a model first</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice <span class="text-danger">*</span></label>
                            <input type="file" name="invoice" class="form-control" accept=".jpg,.jpeg,.pdf" required>
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
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-2"></i>Deduct Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
