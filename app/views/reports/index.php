<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<div class="mb-4 fade-in-up">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-chart-pie me-2"></i> Financial Reports</h2>
    <p class="text-muted small">Generate standard financial reports for your company.</p>
</div>

<div class="row g-4">
    <!-- Trial Balance -->
    <div class="col-md-6 col-lg-3 fade-in-up" style="animation-delay: 0.05s;">
        <a href="<?php echo APP_URL; ?>/reports/trial_balance" class="text-decoration-none">
            <div class="neu-card h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                    style="width: 70px; height: 70px; box-shadow: var(--neumorphic-flat); color: var(--primary);">
                    <i class="fas fa-balance-scale fa-2x"></i>
                </div>
                <h5 class="fw-bold text-secondary">Trial Balance</h5>
                <p class="text-muted small mb-0">Ledger closing balances</p>
            </div>
        </a>
    </div>

    <!-- Ledger Statement -->
    <div class="col-md-6 col-lg-3 fade-in-up" style="animation-delay: 0.1s;">
        <a href="<?php echo APP_URL; ?>/reports/ledger_statement" class="text-decoration-none">
            <div class="neu-card h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-warning"
                    style="width: 70px; height: 70px; box-shadow: var(--neumorphic-flat);">
                    <i class="fas fa-book-open fa-2x"></i>
                </div>
                <h5 class="fw-bold text-secondary">Ledger Stmt</h5>
                <p class="text-muted small mb-0">Detailed list of transactions</p>
            </div>
        </a>
    </div>

    <!-- Profit & Loss -->
    <div class="col-md-6 col-lg-3 fade-in-up" style="animation-delay: 0.15s;">
        <a href="<?php echo APP_URL; ?>/reports/profit_loss" class="text-decoration-none">
            <div class="neu-card h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-success"
                    style="width: 70px; height: 70px; box-shadow: var(--neumorphic-flat);">
                    <i class="fas fa-file-invoice-dollar fa-2x"></i>
                </div>
                <h5 class="fw-bold text-secondary">Profit & Loss</h5>
                <p class="text-muted small mb-0">Income vs Expenses</p>
            </div>
        </a>
    </div>

    <!-- Balance Sheet -->
    <div class="col-md-6 col-lg-3 fade-in-up" style="animation-delay: 0.2s;">
        <a href="<?php echo APP_URL; ?>/reports/balance_sheet" class="text-decoration-none">
            <div class="neu-card h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-primary"
                    style="width: 70px; height: 70px; box-shadow: var(--neumorphic-flat);">
                    <i class="fas fa-landmark fa-2x"></i>
                </div>
                <h5 class="fw-bold text-secondary">Balance Sheet</h5>
                <p class="text-muted small mb-0">Assets, Liabilities & Equity</p>
            </div>
        </a>
    </div>

    <!-- Day Book -->
    <div class="col-md-6 col-lg-3 fade-in-up" style="animation-delay: 0.25s;">
        <a href="<?php echo APP_URL; ?>/reports/day_book" class="text-decoration-none">
            <div class="neu-card h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-secondary"
                    style="width: 70px; height: 70px; box-shadow: var(--neumorphic-flat);">
                    <i class="fas fa-calendar-day fa-2x"></i>
                </div>
                <h5 class="fw-bold text-secondary">Day Book</h5>
                <p class="text-muted small mb-0">Daily voucher entries</p>
            </div>
        </a>
    </div>

    <!-- Cash Book -->
    <div class="col-md-6 col-lg-3 fade-in-up" style="animation-delay: 0.3s;">
        <a href="<?php echo APP_URL; ?>/reports/book_report/Cash" class="text-decoration-none">
            <div class="neu-card h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-warning"
                    style="width: 70px; height: 70px; box-shadow: var(--neumorphic-flat);">
                    <i class="fas fa-wallet fa-2x"></i>
                </div>
                <h5 class="fw-bold text-secondary">Cash Book</h5>
                <p class="text-muted small mb-0">Cash transactions registry</p>
            </div>
        </a>
    </div>

    <!-- Bank Book -->
    <div class="col-md-6 col-lg-3 fade-in-up" style="animation-delay: 0.35s;">
        <a href="<?php echo APP_URL; ?>/reports/book_report/Bank" class="text-decoration-none">
            <div class="neu-card h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-info"
                    style="width: 70px; height: 70px; box-shadow: var(--neumorphic-flat);">
                    <i class="fas fa-university fa-2x"></i>
                </div>
                <h5 class="fw-bold text-secondary">Bank Book</h5>
                <p class="text-muted small mb-0">Bank transactions registry</p>
            </div>
        </a>
    </div>

    <!-- GST Report -->
    <div class="col-md-6 col-lg-3 fade-in-up" style="animation-delay: 0.4s;">
        <a href="<?php echo APP_URL; ?>/reports/gst_report" class="text-decoration-none">
            <div class="neu-card h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-danger"
                    style="width: 70px; height: 70px; box-shadow: var(--neumorphic-flat);">
                    <i class="fas fa-percent fa-2x"></i>
                </div>
                <h5 class="fw-bold text-secondary">GST Report</h5>
                <p class="text-muted small mb-0">Input vs Output Tax</p>
            </div>
        </a>
    </div>

</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>