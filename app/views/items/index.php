<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up" style="position: relative; z-index: 50;">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-box-open me-2"></i> Products & Services</h2>
    <div>
        <div class="dropdown d-inline-block me-2" style="position: relative; z-index: 100;">
            <button class="btn-neu dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown"
                aria-expanded="false" style="color: var(--text-dark);">
                <i class="fas fa-download me-2 text-secondary"></i> Export
            </button>
            <ul class="dropdown-menu border-0 shadow-lg" aria-labelledby="exportDropdown"
                style="border-radius: 12px; z-index: 1050;">
                <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/items/export/csv"><i
                            class="fas fa-file-csv me-2 text-success"></i> Excel/CSV</a></li>
                <li><a class="dropdown-item py-2" href="#" onclick="window.print()"><i
                            class="fas fa-print me-2 text-primary"></i> Print/PDF</a></li>
            </ul>
        </div>
        <a href="<?php echo APP_URL; ?>/items/create" class="btn-neu btn-neu-primary"><i class="fas fa-plus me-2"></i>
            New Item</a>
    </div>
</div>

<div class="card shadow-none bg-transparent">
    <div class="card-body p-0">
        <div class="neu-card">
            <?php if (empty($data['items'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-secondary mb-3 opacity-50"></i>
                    <h5 class="text-secondary">No items found</h5>
                    <p class="text-muted">Add your first product or service.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-neu">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>HSN/SAC</th>
                                <th class="text-end">Sale Price</th>
                                <th class="text-end">Tax %</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['items'] as $index => $item): ?>
                                <tr
                                    style="animation: fadeInUp 0.5s ease-out forwards; animation-delay: <?php echo $index * 0.05; ?>s; opacity: 0;">
                                    <td>
                                        <div class="fw-bold text-secondary"><?php echo htmlspecialchars($item->name); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($item->sku ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-light text-secondary shadow-sm px-3 py-2">
                                            <?php echo htmlspecialchars($item->type); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($item->hsn_sac ?? '-'); ?></td>
                                    <td class="text-end fw-bold" style="color: var(--primary);">
                                        <?php echo number_format($item->sale_price, 2); ?>
                                    </td>
                                    <td class="text-end text-muted">
                                        <?php echo number_format($item->tax_rate, 2); ?>%
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <a href="<?php echo APP_URL; ?>/items/show/<?php echo $item->id; ?>"
                                                class="btn-neu btn-neu-sm text-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/items/edit/<?php echo $item->id; ?>"
                                                class="btn-neu btn-neu-sm text-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?php echo APP_URL; ?>/items/delete/<?php echo $item->id; ?>"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn-neu btn-neu-sm text-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>