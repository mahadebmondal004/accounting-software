<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Create New Item</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo APP_URL; ?>/items/create" method="post">
                    <?php echo csrf_field(); ?>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Item Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="Product">Product</option>
                                <option value="Service">Service</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">SKU / Code</label>
                            <input type="text" name="sku" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">HSN / SAC Code</label>
                            <input type="text" name="hsn_sac" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" class="form-control" list="units" value="Pcs">
                            <datalist id="units">
                                <option value="Pcs">
                                <option value="Kg">
                                <option value="Mtr">
                                <option value="Box">
                            </datalist>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Sale Price (₹)</label>
                            <input type="number" step="0.01" name="sale_price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Purchase Price (₹)</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">GST Tax Rate (%)</label>
                            <select name="tax_rate" class="form-select">
                                <option value="0">0%</option>
                                <option value="5">5%</option>
                                <option value="12">12%</option>
                                <option value="18" selected>18%</option>
                                <option value="28">28%</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="<?php echo APP_URL; ?>/items/index" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>