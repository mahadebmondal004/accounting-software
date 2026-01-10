<?php require APP_ROOT . '/views/layouts/header.php'; ?>

<style>
    .guide-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #6366f1;
    }

    .guide-section h3 {
        color: #6366f1;
        margin-bottom: 15px;
        font-size: 1.3rem;
    }

    .guide-step {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin: 10px 0;
        border-left: 3px solid #10b981;
    }

    .guide-step strong {
        color: #059669;
    }

    .badge-role {
        font-size: 0.85rem;
        padding: 5px 12px;
    }

    .video-placeholder {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        text-align: center;
        border-radius: 8px;
        margin: 15px 0;
    }
</style>

<div class="mb-4 fade-in-up">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="h3 mb-1 text-gradient"><i class="fas fa-book-open me-2"></i> System User Guide</h2>
            <p class="text-muted mb-0">
                <i class="fas fa-user me-1"></i>
                <?php echo $data['user_name']; ?>
                <?php if ($data['is_super_admin']): ?>
                    <span class="badge bg-danger badge-role">Super Admin</span>
                <?php else: ?>
                    <span class="badge bg-primary badge-role">Company Admin</span>
                <?php endif; ?>
            </p>
        </div>
        <a href="<?php echo APP_URL; ?>/dashboard/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Quick Navigation -->
<div class="card shadow-sm border-0 mb-4 fade-in-up">
    <div class="card-body">
        <h5 class="mb-3"><i class="fas fa-compass me-2 text-primary"></i> Quick Navigation</h5>
        <div class="row g-2">
            <div class="col-md-3">
                <a href="#getting-started" class="btn btn-outline-primary w-100">
                    <i class="fas fa-rocket me-2"></i> Getting Started
                </a>
            </div>
            <div class="col-md-3">
                <a href="#masters" class="btn btn-outline-success w-100">
                    <i class="fas fa-database me-2"></i> Masters Setup
                </a>
            </div>
            <div class="col-md-3">
                <a href="#transactions" class="btn btn-outline-warning w-100">
                    <i class="fas fa-exchange-alt me-2"></i> Transactions
                </a>
            </div>
            <div class="col-md-3">
                <a href="#reports" class="btn btn-outline-info w-100">
                    <i class="fas fa-chart-bar me-2"></i> Reports
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Super Admin Guide -->
<?php if ($data['is_super_admin']): ?>
    <div id="super-admin-guide" class="fade-in-up">
        <div class="guide-section">
            <h3><i class="fas fa-crown me-2"></i> Super Admin Guide</h3>
            <p class="text-muted">As a Super Admin, you have complete control over the entire system.</p>

            <div class="guide-step">
                <strong>Step 1: Create Companies</strong>
                <p class="mb-0">Navigate to <code>Quick Create → New Company</code> to add new companies to the system.</p>
            </div>

            <div class="guide-step">
                <strong>Step 2: Manage Users</strong>
                <p class="mb-0">Go to <code>Settings → Users</code> to add users and assign them to companies with specific
                    roles.</p>
            </div>

            <div class="guide-step">
                <strong>Step 3: Configure Roles & Permissions</strong>
                <p class="mb-0">Visit <code>Settings → Role Management</code> to create custom roles with granular
                    permissions.</p>
            </div>

            <div class="guide-step">
                <strong>Step 4: Switch Between Companies</strong>
                <p class="mb-0">Click on <code>Companies</code> in sidebar to view all companies and select any to manage.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Getting Started -->
<div id="getting-started" class="guide-section fade-in-up">
    <h3><i class="fas fa-rocket me-2"></i> Getting Started</h3>

    <div class="guide-step">
        <strong>1. Select Your Company</strong>
        <p>After login, select the company you want to work with from the Companies page.</p>
        <p class="mb-0"><small class="text-muted">Current Company: <strong>
                    <?php echo $data['company_name']; ?>
                </strong></small></p>
    </div>

    <div class="guide-step">
        <strong>2. Dashboard Overview</strong>
        <p class="mb-0">The dashboard shows real-time statistics:</p>
        <ul class="mt-2 mb-0">
            <li>Total Income & Expenses</li>
            <li>Net Profit/Loss</li>
            <li>Receivables & Payables</li>
            <li>Recent Transactions</li>
        </ul>
    </div>
</div>

<!-- Masters Setup -->
<div id="masters" class="guide-section fade-in-up">
    <h3><i class="fas fa-database me-2"></i> Masters Setup</h3>
    <p class="text-muted">Set up your master data before creating transactions.</p>

    <div class="guide-step">
        <strong>1. Account Groups (Chart of Accounts)</strong>
        <p>Navigate to <code>Masters → Account Groups</code></p>
        <ul class="mb-0">
            <li><strong>Purpose:</strong> Organize your accounts hierarchically</li>
            <li><strong>Examples:</strong> Sundry Debtors, Sundry Creditors, Bank Accounts</li>
            <li><strong>Nature:</strong> Assets, Liabilities, Income, Expenses, Equity</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>2. Add Customers</strong>
        <p>Use <code>Quick Create → New Customer</code></p>
        <ul class="mb-0">
            <li>Enter customer name and contact details</li>
            <li>Set opening balance if any</li>
            <li>Automatically grouped under "Sundry Debtors"</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>3. Add Suppliers</strong>
        <p>Use <code>Quick Create → New Supplier</code></p>
        <ul class="mb-0">
            <li>Enter supplier name and contact details</li>
            <li>Set opening balance if any</li>
            <li>Automatically grouped under "Sundry Creditors"</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>4. Add Products/Services</strong>
        <p>Navigate to <code>Masters → Products/Services</code> or use <code>Quick Create → New Item</code></p>
        <ul class="mb-0">
            <li>Enter item name, HSN/SAC code</li>
            <li>Set sale price, purchase price</li>
            <li>Configure tax rates (GST)</li>
            <li>Track stock quantity</li>
        </ul>
    </div>
</div>

<!-- Transactions -->
<div id="transactions" class="guide-section fade-in-up">
    <h3><i class="fas fa-exchange-alt me-2"></i> Creating Transactions</h3>

    <div class="guide-step">
        <strong>1. Sales Invoice</strong>
        <p>Use <code>Quick Create → Sales Invoice</code> or <code>Transactions → Sales Invoice</code></p>
        <ol class="mb-0">
            <li>Select Customer</li>
            <li>Add Items (products/services)</li>
            <li>Enter Quantity & Rate</li>
            <li>System auto-calculates tax & total</li>
            <li>Save to record sale</li>
        </ol>
    </div>

    <div class="guide-step">
        <strong>2. Purchase Bill</strong>
        <p>Use <code>Quick Create → Purchase Bill</code> or <code>Transactions → Purchase Bill</code></p>
        <ol class="mb-0">
            <li>Select Supplier</li>
            <li>Add Items purchased</li>
            <li>Enter Quantity & Rate</li>
            <li>Review tax calculations</li>
            <li>Save to record purchase</li>
        </ol>
    </div>

    <div class="guide-step">
        <strong>3. Receipt (Money In)</strong>
        <p>Use <code>Quick Create → Receipt (In)</code></p>
        <ul class="mb-0">
            <li>Record money received from customers</li>
            <li>Select payment mode (Cash/Bank)</li>
            <li>Link to invoice if applicable</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>4. Payment (Money Out)</strong>
        <p>Use <code>Quick Create → Payment (Out)</code></p>
        <ul class="mb-0">
            <li>Record money paid to suppliers</li>
            <li>Select payment mode</li>
            <li>Link to bill if applicable</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>5. Estimates/Quotations</strong>
        <p>Navigate to <code>Transactions → Estimates/Quotes</code></p>
        <ul class="mb-0">
            <li>Create quotations for customers</li>
            <li>Set expiry date</li>
            <li>Convert to invoice when approved</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>6. Sales/Purchase Returns</strong>
        <p>Navigate to <code>Transactions → Sales Return</code> or <code>Purchase Return</code></p>
        <ul class="mb-0">
            <li>Issue credit notes for sales returns</li>
            <li>Issue debit notes for purchase returns</li>
            <li>Link to original invoice/bill</li>
        </ul>
    </div>
</div>

<!-- Reports -->
<div id="reports" class="guide-section fade-in-up">
    <h3><i class="fas fa-chart-bar me-2"></i> Reports & Analytics</h3>
    <p class="text-muted">Access comprehensive financial reports from the sidebar <strong>Reports</strong> section.</p>

    <div class="guide-step">
        <strong>1. Trial Balance</strong>
        <p>Navigate to <code>Reports → Trial Balance</code></p>
        <ul class="mb-0">
            <li><strong>Purpose:</strong> Summary of all ledger balances</li>
            <li><strong>Shows:</strong> Debit and Credit totals for all accounts</li>
            <li><strong>Use:</strong> Verify accounting accuracy (Debit = Credit)</li>
            <li><strong>Export:</strong> PDF/Excel available</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>2. Profit & Loss Statement</strong>
        <p>Navigate to <code>Reports → Profit & Loss</code></p>
        <ul class="mb-0">
            <li><strong>Purpose:</strong> Income vs Expenses analysis</li>
            <li><strong>Shows:</strong> Revenue, Costs, Net Profit/Loss</li>
            <li><strong>Period:</strong> Select date range for analysis</li>
            <li><strong>Use:</strong> Business performance evaluation</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>3. Balance Sheet</strong>
        <p>Navigate to <code>Reports → Balance Sheet</code></p>
        <ul class="mb-0">
            <li><strong>Purpose:</strong> Financial position snapshot</li>
            <li><strong>Shows:</strong> Assets, Liabilities, Equity</li>
            <li><strong>Formula:</strong> Assets = Liabilities + Equity</li>
            <li><strong>Use:</strong> Company's financial health</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>4. Ledger Statement</strong>
        <p>Navigate to <code>Reports → Ledger Statement</code></p>
        <ul class="mb-0">
            <li><strong>Purpose:</strong> Account-wise transaction details</li>
            <li><strong>Shows:</strong> All entries for selected ledger</li>
            <li><strong>Filter:</strong> By date range and ledger</li>
            <li><strong>Use:</strong> Customer/Supplier account reconciliation</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>5. Day Book</strong>
        <p>Navigate to <code>Reports → Day Book</code></p>
        <ul class="mb-0">
            <li><strong>Purpose:</strong> Daily transaction register</li>
            <li><strong>Shows:</strong> All vouchers for selected date</li>
            <li><strong>Types:</strong> Sales, Purchase, Receipt, Payment</li>
            <li><strong>Use:</strong> Daily activity tracking</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>6. GST Report</strong>
        <p>Navigate to <code>Reports → GST Report</code></p>
        <ul class="mb-0">
            <li><strong>Purpose:</strong> Tax compliance reporting</li>
            <li><strong>Shows:</strong> CGST, SGST, IGST calculations</li>
            <li><strong>Period:</strong> Monthly/Quarterly</li>
            <li><strong>Use:</strong> GST return filing</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>Dashboard Quick Stats</strong>
        <ul class="mb-0">
            <li><strong>Income:</strong> Total sales revenue</li>
            <li><strong>Expenses:</strong> Total purchase costs</li>
            <li><strong>Profit:</strong> Income - Expenses</li>
            <li><strong>Receivables:</strong> Money customers owe you</li>
            <li><strong>Payables:</strong> Money you owe suppliers</li>
            <li><strong>Recent Activity:</strong> Last 3 transactions</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>Export Options</strong>
        <p class="mb-0">Most reports can be exported to:</p>
        <ul class="mb-0">
            <li><i class="fas fa-file-pdf text-danger me-2"></i> <strong>PDF Format</strong> - For printing and sharing
            </li>
            <li><i class="fas fa-file-excel text-success me-2"></i> <strong>Excel Format</strong> - For further analysis
            </li>
        </ul>
    </div>
</div>

<!-- Settings & Configuration -->
<div id="settings" class="guide-section fade-in-up">
    <h3><i class="fas fa-cog me-2"></i> Settings & Configuration</h3>

    <div class="guide-step">
        <strong>Company Settings</strong>
        <p>Navigate to <code>Settings → Company Settings</code></p>
        <ul class="mb-0">
            <li>Update company details</li>
            <li>Configure financial year</li>
            <li>Set tax preferences</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>User Management</strong>
        <p>Navigate to <code>Settings → Users</code></p>
        <ul class="mb-0">
            <li>Add new users</li>
            <li>Assign roles & permissions</li>
            <li>Manage user access</li>
        </ul>
    </div>

    <div class="guide-step">
        <strong>Role Management</strong>
        <p>Navigate to <code>Settings → Role Management</code></p>
        <ul class="mb-0">
            <li>Create custom roles</li>
            <li>Set granular permissions</li>
            <li>Control feature access</li>
        </ul>
    </div>
</div>

<!-- Tips & Best Practices -->
<div class="guide-section fade-in-up" style="border-left-color: #f59e0b;">
    <h3><i class="fas fa-lightbulb me-2" style="color: #f59e0b;"></i> Tips & Best Practices</h3>

    <div class="alert alert-warning">
        <strong><i class="fas fa-exclamation-triangle me-2"></i> Important Tips:</strong>
        <ul class="mb-0 mt-2">
            <li>Always set up Account Groups before creating ledgers</li>
            <li>Add all customers and suppliers before creating invoices</li>
            <li>Configure products with correct tax rates</li>
            <li>Regularly backup your data</li>
            <li>Review dashboard daily for business insights</li>
            <li>Use estimates/quotations for better sales tracking</li>
        </ul>
    </div>
</div>

<!-- Keyboard Shortcuts -->
<div class="guide-section fade-in-up" style="border-left-color: #8b5cf6;">
    <h3><i class="fas fa-keyboard me-2" style="color: #8b5cf6;"></i> Keyboard Shortcuts</h3>

    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>S</kbd></td>
                        <td>Save Form</td>
                    </tr>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>N</kbd></td>
                        <td>New Entry</td>
                    </tr>
                    <tr>
                        <td><kbd>Esc</kbd></td>
                        <td>Cancel/Close</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td><kbd>Tab</kbd></td>
                        <td>Next Field</td>
                    </tr>
                    <tr>
                        <td><kbd>Shift</kbd> + <kbd>Tab</kbd></td>
                        <td>Previous Field</td>
                    </tr>
                    <tr>
                        <td><kbd>Enter</kbd></td>
                        <td>Submit Form</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Support -->
<div class="guide-section fade-in-up" style="border-left-color: #10b981;">
    <h3><i class="fas fa-life-ring me-2" style="color: #10b981;"></i> Need Help?</h3>
    <p>If you need additional assistance:</p>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="text-center p-3 bg-light rounded">
                <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                <p class="mb-0"><strong>Email Support</strong></p>
                <small class="text-muted">support@codteg.com</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-3 bg-light rounded">
                <i class="fas fa-phone fa-2x text-success mb-2"></i>
                <p class="mb-0"><strong>Phone Support</strong></p>
                <small class="text-muted">+91 7908661123</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-3 bg-light rounded">
                <i class="fas fa-video fa-2x text-danger mb-2"></i>
                <p class="mb-0"><strong>Video Tutorials</strong></p>
                <small class="text-muted">Coming Soon</small>
            </div>
        </div>
    </div>
</div>

<!-- Developer Credit -->
<style>
    .developer-credit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
        max-width: 100%;
    }

    .developer-credit::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: pulse 4s ease-in-out infinite;
        will-change: transform, opacity;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 0.5;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }

    .made-with-love {
        font-size: 1.3rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .heart-beat {
        display: inline-block;
        animation: heartbeat 1.5s ease-in-out infinite;
        color: #ff6b6b;
        font-size: 1.5em;
        filter: drop-shadow(0 0 8px rgba(255, 107, 107, 0.8));
    }

    @keyframes heartbeat {

        0%,
        100% {
            transform: scale(1);
        }

        10%,
        30% {
            transform: scale(1.1);
        }

        20%,
        40% {
            transform: scale(0.9);
        }
    }

    .developer-name {
        font-size: 1.5rem;
        font-weight: 800;
        background: linear-gradient(45deg, #fff, #ffd700, #fff);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shine 3s linear infinite;
        text-decoration: none !important;
        transition: all 0.3s ease;
    }

    .developer-name:hover {
        transform: scale(1.05);
    }

    @keyframes shine {
        to {
            background-position: 200% center;
        }
    }

    .tagline {
        font-size: 0.9rem;
        opacity: 0.9;
        font-style: italic;
    }
</style>

<div class="container-fluid px-3" style="max-width: 100%; overflow-x: hidden;">
    <div class="text-center py-4 mt-5 fade-in-up" style="max-width: 100%;">
        <div class="card shadow-lg border-0 developer-credit" style="max-width: 100%;">
            <div class="card-body py-4" style="position: relative; z-index: 1;">
                <p class="mb-0 text-white made-with-love">
                    <i class="fas fa-code me-2"></i>
                    Made with <span class="heart-beat">❤️</span> by
                    <a href="https://fullstackpro.tech" target="_blank" class="developer-name">
                        Mahadev
                    </a>
                </p>
                <small class="text-white tagline d-block mt-3">
                    <i class="fas fa-globe me-1"></i> Full Stack Developer | Building Amazing Solutions
                </small>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>