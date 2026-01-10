<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up">
    <div>
        <h1 class="h3 mb-0 text-gradient"><i class="fas fa-building me-2"></i> Company Management</h1>
        <?php if (isset($data['is_super_admin']) && $data['is_super_admin']): ?>
            <small class="badge bg-danger">Super Admin - All Companies</small>
        <?php else: ?>
            <small class="text-muted">Your Companies</small>
        <?php endif; ?>
    </div>
    <?php if (isset($data['is_super_admin']) && $data['is_super_admin']): ?>
        <a href="<?php echo APP_URL; ?>/companies/create" class="btn-neu btn-neu-primary">
            <i class="fas fa-plus me-2"></i> New Company
        </a>
    <?php endif; ?>
</div>

<div class="row">
    <?php if (empty($data['companies'])): ?>
        <div class="col-12 text-center text-muted py-5 fade-in-up">
            <div class="neu-card d-inline-block p-5">
                <i class="fas fa-folder-open fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                <h4 class="text-secondary">No companies found.</h4>
                <p>Create one to get started.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($data['companies'] as $index => $company): ?>
            <div class="col-md-4 mb-4 fade-in-up" style="animation-delay: <?php echo ($index * 0.1); ?>s">
                <div class="neu-card h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px; box-shadow: var(--neumorphic-flat); color: var(--primary);">
                            <i class="fas fa-building fa-lg"></i>
                        </div>
                        <div class="dropdown" style="position: relative; z-index: 100;">
                            <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu border-0 shadow-lg" style="border-radius: 12px;">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2 text-info"></i> Edit</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i> Delete</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <h5 class="fw-bold text-secondary mb-2"><?php echo $company->name; ?></h5>
                    <p class="text-muted small mb-3 flex-grow-1">
                        <i class="fas fa-map-marker-alt me-2 text-danger"></i> <?php echo $company->address; ?>
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                        <span class="badge rounded-pill bg-light text-secondary shadow-sm">
                            FY: <?php echo date('Y', strtotime($company->financial_year_start)); ?>
                        </span>
                        <a href="<?php echo APP_URL; ?>/dashboard/index?company_id=<?php echo $company->id; ?>"
                            class="btn-neu btn-neu-sm text-primary text-decoration-none fw-bold small px-3">
                            Open <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>