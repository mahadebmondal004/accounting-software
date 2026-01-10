<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="mb-4 fade-in-up">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-cog me-2"></i> Settings</h2>
</div>

<div class="row g-4 fade-in-up">
    <div class="col-md-3">
        <div class="d-flex flex-column gap-3">
            <a href="<?php echo APP_URL; ?>/settings/index"
                class="btn-neu text-start <?php echo (!isset($data['active_tab']) || $data['active_tab'] == 'profile') ? 'btn-neu-primary text-white' : 'text-secondary'; ?> w-100">
                <i class="fas fa-building me-2"></i> Company Profile
            </a>
            <a href="<?php echo APP_URL; ?>/settings/users"
                class="btn-neu text-start <?php echo (isset($data['active_tab']) && $data['active_tab'] == 'users') ? 'btn-neu-primary text-white' : 'text-secondary'; ?> w-100">
                <i class="fas fa-users me-2"></i> User Management
            </a>
            <a href="<?php echo APP_URL; ?>/roles/index" class="btn-neu text-start text-secondary w-100">
                <i class="fas fa-user-tag me-2"></i> Role Management
            </a>
            <a href="<?php echo APP_URL; ?>/settings/backup" class="btn-neu text-start text-success w-100">
                <i class="fas fa-database me-2"></i> Download Backup
            </a>
        </div>
    </div>

    <div class="col-md-9 fade-in-up" style="animation-delay: 0.1s;">
        <div class="neu-card">
            <div class="mb-4 border-bottom border-light pb-3">
                <h5 class="fw-bold text-secondary"><i class="fas fa-building me-2"></i> Company Profile</h5>
            </div>

            <form action="<?php echo APP_URL; ?>/settings/update_profile" method="post" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-4 shadow-neu rounded-circle d-flex align-items-center justify-content-center overflow-hidden"
                                style="width: 80px; height: 80px; background-color: #f0f3f9;">
                                <?php if (!empty($data['company']->logo_path)): ?>
                                    <img src="<?php echo APP_URL . '/' . $data['company']->logo_path; ?>" alt="Logo"
                                        class="w-100 h-100 object-fit-cover">
                                <?php else: ?>
                                    <i class="fas fa-building fa-2x text-secondary"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label ms-1 text-secondary fw-bold small">Company Logo</label>
                                <input type="file" name="company_logo" class="form-control-neu" accept="image/*">
                                <div class="form-text small ms-1">Recommended size: 200x200px (JPG, PNG)</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">Company Name</label>
                        <input type="text" name="name" class="form-control-neu"
                            value="<?php echo htmlspecialchars($data['company']->name); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">GSTIN</label>
                        <input type="text" name="gstin" class="form-control-neu"
                            value="<?php echo htmlspecialchars($data['company']->gstin ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">Email</label>
                        <div class="position-relative">
                            <i
                                class="fas fa-envelope position-absolute top-50 translate-middle-y ms-3 text-secondary"></i>
                            <input type="email" name="email" class="form-control-neu ps-5"
                                value="<?php echo htmlspecialchars($data['company']->email ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">Phone</label>
                        <div class="position-relative">
                            <i class="fas fa-phone position-absolute top-50 translate-middle-y ms-3 text-secondary"></i>
                            <input type="text" name="phone" class="form-control-neu ps-5"
                                value="<?php echo htmlspecialchars($data['company']->phone ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label ms-1 text-secondary fw-bold small">Address</label>
                        <textarea name="address" class="form-control-neu"
                            rows="3"><?php echo htmlspecialchars($data['company']->address ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label ms-1 text-secondary fw-bold small">City</label>
                        <input type="text" name="city" class="form-control-neu"
                            value="<?php echo htmlspecialchars($data['company']->city ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label ms-1 text-secondary fw-bold small">State</label>
                        <input type="text" name="state" class="form-control-neu"
                            value="<?php echo htmlspecialchars($data['company']->state ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label ms-1 text-secondary fw-bold small">Pincode</label>
                        <input type="text" name="pincode" class="form-control-neu"
                            value="<?php echo htmlspecialchars($data['company']->pincode ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">Country</label>
                        <input type="text" name="country" class="form-control-neu"
                            value="<?php echo htmlspecialchars($data['company']->country ?? 'India'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">PAN Number</label>
                        <input type="text" name="pan_no" class="form-control-neu"
                            value="<?php echo htmlspecialchars($data['company']->pan_no ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">Website</label>
                        <div class="position-relative">
                            <i class="fas fa-globe position-absolute top-50 translate-middle-y ms-3 text-secondary"></i>
                            <input type="text" name="website" class="form-control-neu ps-5"
                                value="<?php echo htmlspecialchars($data['company']->website ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label ms-1 text-secondary fw-bold small">Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="form-control-neu"
                            value="<?php echo htmlspecialchars($data['company']->currency_symbol ?? '₹'); ?>">
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" class="btn-neu btn-neu-primary px-4"><i class="fas fa-save me-2"></i> Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>