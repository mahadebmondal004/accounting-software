<?php if (session_status() === PHP_SESSION_NONE)
    session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>AccuBooks</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/neumorphism.css">
</head>

<body>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <!-- Sidebar -->
                <nav class="col-md-3 col-lg-2 d-md-block sidebar-neu collapse sidebar-fixed-height" id="sidebarMenu">
                    <div class="sidebar-header-fixed pt-3 px-3 w-100 text-center">
                        <?php if (!empty($_SESSION['company_logo'])): ?>
                            <div class="mx-auto rounded-3 d-flex align-items-center justify-content-center overflow-hidden"
                                style="width: 140px; height: 60px; padding: 0;">
                                <img src="<?php echo APP_URL . '/' . $_SESSION['company_logo']; ?>" alt="Logo"
                                    class="w-100 h-100 object-fit-contain">
                            </div>

                        <?php else: ?>
                            <div class="brand-text mb-0 pt-2">
                                <i class="fas fa-chart-line me-2"></i> AccuBooks
                            </div>

                        <?php endif; ?>
                    </div>

                    <div class="sidebar-scroll-area">
                        <ul class="nav flex-column mb-5">
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (!isset($data['nav']) || $data['nav'] == 'dashboard') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/dashboard/index">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'companies') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/companies/index">
                                    <i class="fas fa-building"></i> Companies
                                </a>
                            </li>

                            <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase ms-3">Transactions</li>


                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'estimates') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/estimates/index">
                                    <i class="fas fa-file-alt text-warning"></i> Estimates / Quotes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'sales') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/sales/create">
                                    <i class="fas fa-file-invoice"></i> Sales Invoice
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'purchases') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/purchases/create">
                                    <i class="fas fa-shopping-cart"></i> Purchase Bill
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && isset($data['type']) && $data['type'] == 'Credit Note') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/returns/sales_return">
                                    <i class="fas fa-undo text-primary"></i> Sales Return
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && isset($data['type']) && $data['type'] == 'Debit Note') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/returns/purchase_return">
                                    <i class="fas fa-undo text-secondary"></i> Purchase Return
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && isset($data['type']) && $data['type'] == 'Receipt') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/receipts/create">
                                    <i class="fas fa-hand-holding-usd text-success"></i> Money In
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && isset($data['type']) && $data['type'] == 'Payment') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/payments/create">
                                    <i class="fas fa-money-bill-wave text-danger"></i> Money Out
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && isset($data['type']) && $data['type'] == 'Contra') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/transfers/create">
                                    <i class="fas fa-exchange-alt text-warning"></i> Transfer Money
                                </a>
                            </li>

                            <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase ms-3">Analysis</li>

                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'reports') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/reports/index">
                                    <i class="fas fa-chart-pie"></i> Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'vouchers' && !isset($data['type'])) ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/vouchers/index">
                                    <i class="fas fa-list"></i> All Transactions
                                </a>
                            </li>

                            <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase ms-3">Masters</li>

                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'items') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/items/index">
                                    <i class="fas fa-boxes"></i> Products/Services
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'account_groups') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/account_groups/index">
                                    <i class="fas fa-sitemap text-info"></i> Account Groups
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'ledgers') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/ledgers/index">
                                    <i class="fas fa-book"></i> All Accounts
                                </a>
                                </a>
                            </li>

                            <!-- Reports Section -->
                            <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase ms-3">Reports</li>

                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'trial_balance') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/reports/trial_balance">
                                    <i class="fas fa-balance-scale text-info"></i> Trial Balance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'profit_loss') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/reports/profit_loss">
                                    <i class="fas fa-chart-line text-success"></i> Profit & Loss
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'balance_sheet') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/reports/balance_sheet">
                                    <i class="fas fa-file-invoice-dollar text-primary"></i> Balance Sheet
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'ledger_statement') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/reports/ledger_statement">
                                    <i class="fas fa-book-open text-warning"></i> Ledger Statement
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'day_book') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/reports/day_book">
                                    <i class="fas fa-calendar-day text-secondary"></i> Day Book
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'gst_report') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/reports/gst_report">
                                    <i class="fas fa-receipt text-danger"></i> GST Report
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link-neu <?php echo (isset($data['nav']) && $data['nav'] == 'settings') ? 'active' : ''; ?>"
                                    href="<?php echo APP_URL; ?>/settings/index">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </li>
                            <li class="nav-item mt-4 pb-5">
                                <a class="nav-link-neu text-danger" href="<?php echo APP_URL; ?>/auth/logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Main Content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content offset-md-3 offset-lg-2 py-4">
                    <nav class="navbar-neu d-flex justify-content-between align-items-center fade-in-up"
                        style="position: relative; z-index: 1000;">
                        <div class="d-flex align-items-center">
                            <button class="navbar-toggler d-md-none collapsed me-3" type="button" data-bs-toggle="collapse"
                                data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <span class="h4 mb-0 text-gradient"><?php echo isset($title) ? $title : 'Dashboard'; ?></span>
                        </div>
                        <div class="d-flex align-items-center">

                            <!-- Quick Create Dropdown -->
                            <div class="dropdown me-4">
                                <button class="btn-neu btn-neu-sm btn-neu-primary dropdown-toggle px-3" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="fas fa-plus me-1"></i> Quick Create
                                </button>
                                <ul class="dropdown-menu border-0 shadow-lg mt-2"
                                    style="border-radius: 12px; min-width: 200px; z-index: 1050;">
                                    <li>
                                        <h6 class="dropdown-header text-uppercase small fw-bold text-muted">Transactions
                                        </h6>
                                    </li>
                                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/sales/create"><i
                                                class="fas fa-file-invoice text-primary me-2"></i> Sales Invoice</a></li>
                                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/purchases/create"><i
                                                class="fas fa-shopping-cart text-secondary me-2"></i> Purchase Bill</a></li>
                                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/receipts/create"><i
                                                class="fas fa-hand-holding-usd text-success me-2"></i> Receipt (In)</a></li>
                                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/payments/create"><i
                                                class="fas fa-money-bill-wave text-danger me-2"></i> Payment (Out)</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header text-uppercase small fw-bold text-muted">Masters</h6>
                                    </li>
                                    <?php if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] == 1): ?>
                                        <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/companies/create"><i
                                                    class="fas fa-building text-info me-2"></i> New Company</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item py-2"
                                            href="<?php echo APP_URL; ?>/ledgers/create?type=Customer"><i
                                                class="fas fa-user-plus text-success me-2"></i> New Customer</a></li>
                                    <li><a class="dropdown-item py-2"
                                            href="<?php echo APP_URL; ?>/ledgers/create?type=Supplier"><i
                                                class="fas fa-truck-loading text-warning me-2"></i> New Supplier</a></li>
                                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/items/create"><i
                                                class="fas fa-box text-warning me-2"></i> New Item</a></li>
                                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/ledgers/create"><i
                                                class="fas fa-book text-dark me-2"></i> New Ledger</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/guide/index"><i
                                                class="fas fa-book-open text-primary me-2"></i> <strong>User
                                                Guide</strong></a></li>
                                </ul>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="me-3 text-end d-none d-md-block">
                                    <div class="d-flex flex-column align-items-end text-decoration-none">
                                        <div class="fw-bold d-flex align-items-center"
                                            style="color:var(--primary); line-height: 1.2;">
                                            <?php echo $_SESSION['user_name']; ?>
                                        </div>
                                        <small class="text-muted d-flex align-items-center" style="font-size: 0.75rem;">
                                            <span class="online-pulse"></span> Online
                                        </small>
                                    </div>
                                </div>
                                <a href="<?php echo APP_URL; ?>/profile/index" class="text-decoration-none ms-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center overflow-hidden"
                                        style="width:40px; height:40px; box-shadow: var(--neumorphic-flat); color: var(--primary); padding: 2px;">
                                        <?php if (!empty($_SESSION['user_profile_pic'])): ?>
                                            <img src="<?php echo APP_URL . '/' . $_SESSION['user_profile_pic']; ?>" alt="User"
                                                class="w-100 h-100 rounded-circle object-fit-cover">
                                        <?php else: ?>
                                            <i class="fas fa-user"></i>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </nav>
                <?php endif; ?>