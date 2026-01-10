<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i> Item Details</h5>
                <a href="<?php echo APP_URL; ?>/items/edit/<?php echo $data['item']->id; ?>"
                    class="btn btn-light btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="text-muted small">Item Name</label>
                        <p class="fw-bold"><?php echo htmlspecialchars($data['item']->name); ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Type</label>
                        <p><span class="badge bg-light text-dark border"><?php echo $data['item']->type; ?></span></p>
                    </div>

                    <div class="col-md-4">
                        <label class="text-muted small">SKU / Code</label>
                        <p><?php echo htmlspecialchars($data['item']->sku ?? '-'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">HSN / SAC Code</label>
                        <p><?php echo htmlspecialchars($data['item']->hsn_sac ?? '-'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Unit</label>
                        <p><?php echo htmlspecialchars($data['item']->unit ?? 'Pcs'); ?></p>
                    </div>

                    <div class="col-md-4">
                        <label class="text-muted small">Sale Price</label>
                        <p class="text-success fw-bold">₹ <?php echo number_format($data['item']->sale_price, 2); ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Purchase Price</label>
                        <p class="text-danger fw-bold">₹ <?php echo number_format($data['item']->purchase_price, 2); ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Tax Rate</label>
                        <p><?php echo number_format($data['item']->tax_rate, 2); ?>%</p>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Opening Stock</label>
                        <p><?php echo number_format($data['item']->opening_stock ?? 0, 2); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Current Stock</label>
                        <p class="fw-bold"><?php echo number_format($data['item']->current_stock ?? 0, 2); ?></p>
                    </div>

                    <?php if (!empty($data['item']->description)): ?>
                        <div class="col-12">
                            <label class="text-muted small">Description</label>
                            <p><?php echo nl2br(htmlspecialchars($data['item']->description)); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <a href="<?php echo APP_URL; ?>/items/index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>