-- Database Schema for Accounting Software

-- Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    permissions TEXT, -- JSON structure of permissions
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    recovery_token VARCHAR(100),
    profile_pic VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- Companies Table
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    logo VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(20),
    country VARCHAR(100) DEFAULT 'India',
    gstin VARCHAR(20),
    pan_no VARCHAR(20),
    email VARCHAR(150),
    phone VARCHAR(20),
    website VARCHAR(150),
    currency_symbol VARCHAR(10) DEFAULT '₹',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User Company Access (Many-to-Many)
CREATE TABLE IF NOT EXISTS user_companies (
    user_id INT NOT NULL,
    company_id INT NOT NULL,
    PRIMARY KEY (user_id, company_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Financial Years
CREATE TABLE IF NOT EXISTS financial_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(50) NOT NULL, -- e.g., "2024-2025"
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Account Groups (Chart of Accounts)
CREATE TABLE IF NOT EXISTS account_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    parent_id INT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    nature ENUM('Assets', 'Liabilities', 'Equity', 'Income', 'Expenses') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES account_groups(id) ON DELETE SET NULL
);

-- Ledgers
CREATE TABLE IF NOT EXISTS ledgers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    group_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(20),
    type ENUM('General', 'Customer', 'Supplier', 'Bank', 'Cash', 'Fixed Asset', 'Tax', 'Bank_Account') DEFAULT 'General',
    opening_balance DECIMAL(15, 2) DEFAULT 0.00,
    opening_balance_type ENUM('Dr', 'Cr') DEFAULT 'Dr',
    current_balance DECIMAL(15, 2) DEFAULT 0.00, -- Cached value
    reconciliation_date DATE NULL, -- For bank reconciliation
    contact_person VARCHAR(100),
    email VARCHAR(150),
    phone VARCHAR(20),
    address TEXT,
    gstin VARCHAR(20),
    pan_no VARCHAR(20),
    bank_account_details TEXT, -- JSON for bank details if type is Customer/Supplier
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES account_groups(id) ON DELETE RESTRICT
);

-- Vouchers (Head)
CREATE TABLE IF NOT EXISTS vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    financial_year_id INT NOT NULL,
    voucher_type ENUM('Payment', 'Receipt', 'Journal', 'Contra', 'Sales', 'Purchase', 'Debit Note', 'Credit Note') NOT NULL,
    voucher_number VARCHAR(50) NOT NULL,
    voucher_date DATE NOT NULL,
    narration TEXT,
    items_amount DECIMAL(15,2) DEFAULT 0.00, -- Before tax
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    attachment_path VARCHAR(255),
    created_by INT NOT NULL,
    approved_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (financial_year_id) REFERENCES financial_years(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT 
);

-- Voucher Entries (Rows - Double Entry)
CREATE TABLE IF NOT EXISTS voucher_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucher_id INT NOT NULL,
    ledger_id INT NOT NULL,
    debit DECIMAL(15, 2) DEFAULT 0.00,
    credit DECIMAL(15, 2) DEFAULT 0.00,
    description VARCHAR(255), -- Line item narration
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE,
    FOREIGN KEY (ledger_id) REFERENCES ledgers(id) ON DELETE RESTRICT
);

-- Products/Services (Item Master)
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    type ENUM('Product', 'Service') NOT NULL,
    sku VARCHAR(50),
    hsn_sac VARCHAR(20),
    unit VARCHAR(20), -- e.g., Pcs, Kg
    sale_price DECIMAL(15, 2) DEFAULT 0.00,
    purchase_price DECIMAL(15, 2) DEFAULT 0.00,
    tax_rate DECIMAL(5, 2) DEFAULT 0.00, -- GST %
    description TEXT,
    opening_stock DECIMAL(15, 2) DEFAULT 0.00,
    current_stock DECIMAL(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Invoice Extra Details (Linking to Voucher for Sales/Purchase specific data)
-- In a strict double entry accounting, Sales/Purchase are just Vouchers. 
-- However, for Printing an Invoice, we need more detail (Items, Qty, Rate).
CREATE TABLE IF NOT EXISTS invoice_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucher_id INT NOT NULL UNIQUE, -- Links to the main voucher
    due_date DATE,
    po_number VARCHAR(50), -- Purchase Order Ref
    terms_conditions TEXT,
    shipping_address TEXT,
    transport_mode VARCHAR(50),
    vehicle_number VARCHAR(20),
    eway_bill_no VARCHAR(50),
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE
);

-- Invoice Items (Line items for the invoice print, these eventually sum up to Ledger entries)
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucher_id INT NOT NULL,
    item_id INT NULL, -- Link to item master
    item_name VARCHAR(200) NOT NULL, -- Snapshot of name
    quantity DECIMAL(15, 2) NOT NULL,
    rate DECIMAL(15, 2) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL, -- Qty * Rate
    discount_percent DECIMAL(5, 2) DEFAULT 0.00,
    discount_amount DECIMAL(15, 2) DEFAULT 0.00,
    tax_percent DECIMAL(5, 2) DEFAULT 0.00,
    tax_amount DECIMAL(15, 2) DEFAULT 0.00,
    cgst_amount DECIMAL(15, 2) DEFAULT 0.00,
    sgst_amount DECIMAL(15, 2) DEFAULT 0.00,
    igst_amount DECIMAL(15, 2) DEFAULT 0.00,
    total DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL
);

-- Bank Reconciliation
CREATE TABLE IF NOT EXISTS bank_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ledger_id INT NOT NULL, -- The bank ledger
    transaction_date DATE,
    description VARCHAR(255),
    debit DECIMAL(15, 2) DEFAULT 0.00, -- As per bank statement
    credit DECIMAL(15, 2) DEFAULT 0.00, -- As per bank statement
    balance DECIMAL(15, 2),
    is_reconciled TINYINT(1) DEFAULT 0,
    voucher_id INT NULL, -- Link if matched
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ledger_id) REFERENCES ledgers(id) ON DELETE CASCADE
);

-- Audit Logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NULL,
    user_id INT NULL,
    action_type VARCHAR(50), -- LOGIN, INSERT, UPDATE, DELETE
    module VARCHAR(50), -- VOUCHER, LEDGER, USER
    record_id INT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Data: Roles
INSERT INTO roles (id, name, permissions) VALUES 
(1, 'Super Admin', '{"all": true}'),
(2, 'Company Admin', '{"manage_company": true, "manage_users": true, "accounting": true}'),
(3, 'Accountant', '{"accounting": true, "reports": true}'),
(4, 'Data Entry Operator', '{"entry": true}'),
(5, 'Client', '{"view_reports": true}')
ON DUPLICATE KEY UPDATE name=name;

-- Indexes (Optimization)
CREATE INDEX idx_voucher_date ON vouchers(voucher_date);
CREATE INDEX idx_voucher_type ON vouchers(voucher_type);
CREATE INDEX idx_ledger_group ON ledgers(group_id);
CREATE INDEX idx_entry_ledger ON voucher_entries(ledger_id);


-- ==================== DEMO DATA ====================

-- 1. Insert User (Admin) - Password: 'password'
INSERT IGNORE INTO users (role_id, name, email, password, is_active) VALUES 
(1, 'Super Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- 2. Insert Company
INSERT IGNORE INTO companies (name, email, address, country, currency_symbol) VALUES 
('Demo Company Pvt Ltd', 'info@democompany.com', '123, Tech Park, Mumbai', 'India', '₹');

-- 3. Link User to Company
INSERT IGNORE INTO user_companies (user_id, company_id) VALUES (1, 1);

-- 4. Financial Year
INSERT IGNORE INTO financial_years (company_id, name, start_date, end_date, is_active) VALUES 
(1, '2024-2025', '2024-04-01', '2025-03-31', 1);

-- 5. Account Groups (Minimal Set)
INSERT IGNORE INTO account_groups (id, company_id, parent_id, name, nature) VALUES 
(1, 1, NULL, 'Assets', 'Assets'),
(2, 1, NULL, 'Liabilities', 'Liabilities'),
(3, 1, NULL, 'Income', 'Income'),
(4, 1, NULL, 'Expenses', 'Expenses'),
(5, 1, NULL, 'Equity', 'Equity');

-- Sub Groups
INSERT IGNORE INTO account_groups (id, company_id, parent_id, name, nature) VALUES 
(6, 1, 1, 'Current Assets', 'Assets'),
(7, 1, 6, 'Cash-in-hand', 'Assets'),
(8, 1, 6, 'Bank Accounts', 'Assets'),
(9, 1, 6, 'Sundry Debtors', 'Assets'),
(10, 1, 2, 'Current Liabilities', 'Liabilities'),
(11, 1, 2, 'Sundry Creditors', 'Liabilities'),
(12, 1, 2, 'Duties & Taxes', 'Liabilities'),
(13, 1, 3, 'Sales Accounts', 'Income'),
(14, 1, 4, 'Purchase Accounts', 'Expenses'),
(15, 1, 4, 'Indirect Expenses', 'Expenses');

-- 6. Ledgers
INSERT IGNORE INTO ledgers (company_id, group_id, name, type, opening_balance) VALUES 
(1, 7, 'Cash', 'Cash', 50000.00),
(1, 8, 'HDFC Bank', 'Bank', 150000.00),
(1, 13, 'Sales', 'General', 0.00),
(1, 14, 'Purchases', 'General', 0.00),
(1, 9, 'Rahul Traders (Customer)', 'Customer', 0.00),
(1, 11, 'Smart Supplies (Vendor)', 'Supplier', 0.00),
(1, 12, 'IGST Output', 'Tax', 0.00),
(1, 12, 'CGST Output', 'Tax', 0.00),
(1, 12, 'SGST Output', 'Tax', 0.00),
(1, 15, 'Office Rent', 'General', 0.00);

-- 7. Items
INSERT IGNORE INTO items (company_id, name, type, sku, sale_price, purchase_price, current_stock) VALUES 
(1, 'Dell Laptop', 'Product', 'DELL001', 45000.00, 35000.00, 10),
(1, 'Wireless Mouse', 'Product', 'LOGI002', 800.00, 500.00, 50),
(1, 'IT Support', 'Service', 'SERV001', 1000.00, 0.00, 0);

-- 8. Vouchers
INSERT IGNORE INTO vouchers (company_id, financial_year_id, voucher_type, voucher_number, voucher_date, narration, total_amount, created_by) VALUES 
(1, 1, 'Purchase', 'PUR-001', '2024-04-10', 'Purchased 5 Laptops from Smart Supplies', 175000.00, 1),
(1, 1, 'Sales', 'INV-001', '2024-04-15', 'Sold 2 Laptops to Rahul Traders', 90000.00, 1),
(1, 1, 'Receipt', 'REC-001', '2024-04-20', 'Received payment from Rahul Traders', 50000.00, 1),
(1, 1, 'Payment', 'PAY-001', '2024-04-25', 'Paid to Smart Supplies', 50000.00, 1);

-- 9. Voucher Entries
INSERT IGNORE INTO voucher_entries (voucher_id, ledger_id, debit, credit, description) VALUES 
(1, 4, 175000.00, 0.00, 'Purchase 5 Laptops'), (1, 6, 0.00, 175000.00, 'Credit Purchase'),
(2, 5, 90000.00, 0.00, 'Sold 2 Laptops'), (2, 3, 0.00, 90000.00, 'Sales Account'),
(3, 2, 50000.00, 0.00, 'Check Received'), (3, 5, 0.00, 50000.00, 'Payment on Account'),
(4, 6, 50000.00, 0.00, 'Payment for Bill'), (4, 2, 0.00, 50000.00, 'Check Issued');
