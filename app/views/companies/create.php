<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-9 fade-in-up">
        <div class="neu-card">
            <div class="mb-4 text-center border-bottom border-light pb-3">
                <h2 class="text-gradient h3"><i class="fas fa-plus-circle me-2"></i> Create New Company</h2>
                <p class="text-muted small">Enter your business details to start managing accounts.</p>
            </div>

            <form action="<?php echo APP_URL; ?>/companies/create" method="post">
                <?php echo csrf_field(); ?>
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label ms-1 text-secondary fw-bold small">Company Name *</label>
                        <input type="text" name="name" class="form-control-neu" required
                            placeholder="e.g. ABC Technologies Pvt Ltd">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">GSTIN / Tax ID</label>
                        <input type="text" name="gstin" class="form-control-neu" placeholder="YOUR GSTIN">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">Financial Year Start *</label>
                        <input type="date" name="financial_year_start" class="form-control-neu"
                            value="<?php echo date('Y-04-01'); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">Email</label>
                        <div class="position-relative">
                            <i
                                class="fas fa-envelope position-absolute top-50 translate-middle-y ms-3 text-secondary"></i>
                            <input type="email" name="email" class="form-control-neu ps-5">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">Phone</label>
                        <div class="position-relative">
                            <i class="fas fa-phone position-absolute top-50 translate-middle-y ms-3 text-secondary"></i>
                            <input type="text" name="phone" class="form-control-neu ps-5">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label ms-1 text-secondary fw-bold small">Address</label>
                        <textarea name="address" class="form-control-neu" rows="3"></textarea>
                    </div>
                </div>

                <div class="mt-5 d-flex justify-content-end gap-3">
                    <a href="<?php echo APP_URL; ?>/companies/index" class="btn-neu" style="color: var(--danger);">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn-neu btn-neu-primary px-4">
                        <i class="fas fa-check me-2"></i> Create Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>