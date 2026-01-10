<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<!-- Dashboard Header with Date Filter -->
<div class="d-flex justify-content-between align-items-center mb-4 fade-in-up" style="position: relative; z-index: 200;">
    <h2 class="h3 mb-0 text-gradient"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</h2>

    <div class="dropdown" style="position: relative; z-index: 100;">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dateFilterDropdown"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-calendar-alt me-2"></i>
            <span id="selectedPeriod"><?php echo $data['period_label']; ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dateFilterDropdown" style="z-index: 1050;">
            <li><a class="dropdown-item <?php echo $data['period'] == 'today' ? 'active' : ''; ?>" href="?period=today">
                    <i class="fas fa-calendar-day me-2 text-primary"></i> Today</a></li>
            <li><a class="dropdown-item <?php echo $data['period'] == 'week' ? 'active' : ''; ?>" href="?period=week">
                    <i class="fas fa-calendar-week me-2 text-success"></i> This Week</a></li>
            <li><a class="dropdown-item <?php echo $data['period'] == 'month' ? 'active' : ''; ?>" href="?period=month">
                    <i class="fas fa-calendar me-2 text-info"></i> This Month</a></li>
            <li><a class="dropdown-item <?php echo $data['period'] == 'year' ? 'active' : ''; ?>" href="?period=year">
                    <i class="fas fa-calendar-alt me-2 text-warning"></i> This Year</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item <?php echo $data['period'] == 'lifetime' ? 'active' : ''; ?>"
                    href="?period=lifetime">
                    <i class="fas fa-infinity me-2 text-danger"></i> Lifetime</a></li>
        </ul>
    </div>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-3 fade-in-up" style="animation-delay: 0.1s;">
        <div class="neu-card h-100 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase text-secondary small fw-bold mb-2">Total Income</h6>
                    <h3 class="mb-0 fw-bold text-success">₹ <?php echo number_format($data['income']); ?></h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px; box-shadow: var(--neumorphic-pressed); color: #2ecc71;">
                    <i class="fas fa-arrow-up fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 fade-in-up" style="animation-delay: 0.2s;">
        <div class="neu-card h-100 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase text-secondary small fw-bold mb-2">Total Expense</h6>
                    <h3 class="mb-0 fw-bold text-danger">₹ <?php echo number_format($data['expenses']); ?></h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px; box-shadow: var(--neumorphic-pressed); color: #e74c3c;">
                    <i class="fas fa-arrow-down fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 fade-in-up" style="animation-delay: 0.3s;">
        <div class="neu-card h-100 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase text-secondary small fw-bold mb-2">Net Profit</h6>
                    <h3 class="mb-0 fw-bold text-primary">₹ <?php echo number_format($data['profit']); ?></h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px; box-shadow: var(--neumorphic-pressed); color: var(--primary);">
                    <i class="fas fa-wallet fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 fade-in-up" style="animation-delay: 0.4s;">
        <div class="neu-card h-100 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase text-secondary small fw-bold mb-2">Receivables</h6>
                    <h3 class="mb-0 fw-bold text-warning">₹ <?php echo number_format($data['receivables']); ?></h3>
                    <small class="text-danger" style="font-size: 0.75rem;">Payables: ₹
                        <?php echo number_format($data['payables']); ?></small>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px; box-shadow: var(--neumorphic-pressed); color: #f1c40f;">
                    <i class="fas fa-hand-holding-usd fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4 fade-in-up" style="animation-delay: 0.5s;">
        <div class="neu-card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1">Customers</h6>
                    <h4 class="mb-0 fw-bold text-info"><?php echo $data['total_customers']; ?></h4>
                </div>
                <div class="text-info"><i class="fas fa-users fa-2x"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 fade-in-up" style="animation-delay: 0.6s;">
        <div class="neu-card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1">Suppliers</h6>
                    <h4 class="mb-0 fw-bold text-warning"><?php echo $data['total_suppliers']; ?></h4>
                </div>
                <div class="text-warning"><i class="fas fa-truck fa-2x"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 fade-in-up" style="animation-delay: 0.7s;">
        <div class="neu-card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1">Products/Items</h6>
                    <h4 class="mb-0 fw-bold text-secondary"><?php echo $data['total_items']; ?></h4>
                </div>
                <div class="text-secondary"><i class="fas fa-boxes fa-2x"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <div class="col-md-8 fade-in-up" style="animation-delay: 0.5s;">
        <div class="neu-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="h5 mb-0 text-secondary">Income vs Expense</span>
                <select class="form-control-neu py-1 px-3" style="width: auto; font-size: 0.85rem;">
                    <option>Last 12 Months</option>
                    <option>This Year</option>
                </select>
            </div>
            <div class="card-body">
                <canvas id="incomeExpenseChart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4 fade-in-up" style="animation-delay: 0.6s;">
        <div class="neu-card h-100">
            <div class="h5 mb-4 text-secondary text-center">Expense Breakdown</div>
            <div class="card-body d-flex justify-content-center">
                <canvas id="expenseDoughnutChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links & Recent Activity -->
<div class="row g-4">
    <div class="col-md-6 fade-in-up" style="animation-delay: 0.7s;">
        <div class="neu-card h-100">
            <div class="h5 mb-4 text-secondary">Quick Actions</div>
            <div class="card-body">
                <div class="d-grid gap-3 d-md-flex justify-content-md-start flex-wrap">
                    <a href="<?php echo APP_URL; ?>/sales/create" class="btn-neu"><i
                            class="fas fa-plus me-2 text-primary"></i> New Invoice</a>
                    <a href="<?php echo APP_URL; ?>/purchases/create" class="btn-neu"><i
                            class="fas fa-shopping-cart me-2 text-secondary"></i> New Purchase</a>
                    <a href="<?php echo APP_URL; ?>/payments/create" class="btn-neu"><i
                            class="fas fa-money-bill-wave me-2 text-danger"></i> Record Expense</a>
                    <a href="<?php echo APP_URL; ?>/receipts/create" class="btn-neu"><i
                            class="fas fa-hand-holding-usd me-2 text-success"></i> Record Receipt</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 fade-in-up" style="animation-delay: 0.8s;">
        <div class="neu-card h-100">
            <div class="h5 mb-4 text-secondary">Recent Activity</div>
            <div class="card-body p-0">
                <?php if (!empty($data['recent_invoices'])): ?>
                    <?php foreach ($data['recent_invoices'] as $invoice): ?>
                        <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-start mb-3"
                            style="box-shadow: var(--neumorphic-pressed); border-radius: 10px; padding: 15px;">
                            <div class="ms-2 me-auto">
                                <div
                                    class="fw-bold <?php echo $invoice->voucher_type == 'Sales' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $invoice->voucher_type == 'Sales' ? '📈 Sales' : '📉 Purchase'; ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($invoice->narration ?: $invoice->voucher_number); ?>
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('d M Y', strtotime($invoice->voucher_date)); ?>
                                </small>
                            </div>
                            <span
                                class="badge rounded-pill <?php echo $invoice->voucher_type == 'Sales' ? 'bg-success' : 'bg-danger'; ?> shadow-sm mt-1">
                                ₹
                                <?php echo number_format($invoice->voucher_type == 'Sales' ? $invoice->total_credit : $invoice->total_debit); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item bg-transparent border-0 text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No recent transactions</p>
                    </li>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Bar Chart
        const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Income',
                    data: [12000, 19000, 3000, 5000, 2000, 3000],
                    backgroundColor: '#6c5ce7',
                    borderRadius: 10,
                    barThickness: 20
                }, {
                    label: 'Expense',
                    data: [2000, 3000, 2000, 3000, 2000, 3000],
                    backgroundColor: '#ff7675',
                    borderRadius: 10,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true,
                            font: { family: 'Inter' }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: 'rgba(0,0,0,0.05)' },
                        ticks: { font: { family: 'Inter' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter' } }
                    }
                }
            }
        });

        // Doughnut Chart
        const ctx2 = document.getElementById('expenseDoughnutChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Rent', 'Salaries', 'Utilities', 'Others'],
                datasets: [{
                    data: [30, 40, 15, 15],
                    backgroundColor: ['#f1c40f', '#e67e22', '#1abc9c', '#9b59b6'],
                    borderWidth: 5,
                    borderColor: '#e0e5ec' // Match bg for spacing look
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }
            }
        });
    });

    // Handle period filter dropdown
    document.querySelectorAll('.dropdown-menu a[href^="?period="]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.getAttribute('href').split('=')[1];
            window.location.href = '<?php echo APP_URL; ?>/dashboard/index?period=' + period;
        });
    });
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>