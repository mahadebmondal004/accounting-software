# 📊 AccuBooks - Professional Accounting Software

A complete, feature-rich accounting software built with PHP following MVC architecture. Perfect for small to medium businesses to manage their finances efficiently.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## ✨ Features

### 📈 Core Accounting
- **Multi-Company Support** - Manage multiple companies from single installation
- **Double Entry Accounting** - Complete voucher-based accounting system
- **Account Groups** - Hierarchical chart of accounts
- **Ledger Management** - Customers, Suppliers, and General ledgers
- **Product/Service Management** - Inventory tracking with items

### 💰 Transactions
- **Sales Invoice** - Create and manage sales with GST
- **Purchase Bill** - Record purchases and expenses
- **Receipt & Payment** - Cash/Bank transactions
- **Estimates/Quotations** - Generate professional quotes
- **Sales/Purchase Returns** - Handle return transactions
- **Money Transfer** - Inter-account transfers

### 📊 Reports & Analytics
- **Trial Balance** - Verify accounting accuracy
- **Profit & Loss Statement** - Income vs Expenses analysis
- **Balance Sheet** - Financial position snapshot
- **Ledger Statement** - Account-wise transaction details
- **Day Book** - Daily transaction register
- **GST Report** - Tax compliance reporting
- **Dashboard Analytics** - Real-time business insights

### 🔐 Security Features
- **Session Management** - 30-minute auto-timeout
- **CSRF Protection** - Token-based security
- **Password Hashing** - Bcrypt encryption
- **SQL Injection Prevention** - Prepared statements
- **XSS Protection** - Input sanitization
- **IP Validation** - Session hijacking prevention

### 👥 User Management
- **Role-Based Access Control** - Granular permissions
- **Super Admin** - Complete system control
- **Company Admin** - Company-level management
- **User Roles** - Custom role creation
- **Profile Management** - User settings and preferences

### 🎨 Modern UI/UX
- **Neumorphic Design** - Beautiful soft UI
- **Responsive Layout** - Mobile-friendly interface
- **Dark Mode Ready** - Eye-friendly design
- **Interactive Dashboard** - Charts and graphs
- **Quick Actions** - Fast navigation

## 🚀 Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled

### Local Setup

1. **Clone the repository**
```bash
git clone https://github.com/mahadebmondal004/accounting.git
cd accounting
```

2. **Import Database**
```bash
mysql -u root -p
CREATE DATABASE accubooks;
USE accubooks;
SOURCE database/schema.sql;
```

3. **Configure Application**
```bash
# Copy and edit config file
cp config/config.production.php config/config.php

# Update database credentials in config/config.php
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = 'your_password'
DB_NAME = 'accubooks'
APP_URL = 'http://localhost:8000'
```

4. **Set Permissions**
```bash
chmod 755 storage/sessions
chmod 755 storage/logs
chmod 755 storage/uploads
```

5. **Run Development Server**
```bash
cd public
php -S localhost:8000
```

6. **Access Application**
```
URL: http://localhost:8000
Email: admin@example.com
Password: password
```

## 🌐 Production Deployment (Hostinger)

### Quick Deploy
1. Upload all files to `public_html`
2. Create MySQL database in cPanel
3. Import `database/schema.sql`
4. Update `config/config.php` with production credentials
5. Rename `.htaccess.production` to `.htaccess`
6. Enable SSL certificate
7. Access your domain

**Detailed deployment guide available in repository**

## 📁 Project Structure

```
accounting/
├── app/
│   ├── controllers/     # Application controllers
│   ├── models/         # Database models
│   ├── views/          # View templates
│   └── middleware/     # Security middleware
├── config/             # Configuration files
├── core/               # Core MVC framework
├── database/           # Database schema
├── helpers/            # Helper functions
├── public/             # Public assets
│   ├── assets/        # CSS, JS, Images
│   └── index.php      # Entry point
└── storage/            # Logs, sessions, uploads
```

## 🛠️ Technology Stack

- **Backend:** PHP 7.4+ (Custom MVC)
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **UI Framework:** Bootstrap 5
- **Icons:** Font Awesome 6
- **Charts:** Chart.js
- **Architecture:** MVC Pattern

## 📖 Usage Guide

### Creating Your First Company
1. Login to dashboard
2. Navigate to Companies
3. Click "Add New Company"
4. Fill company details
5. Select company to start

### Recording a Sale
1. Go to Quick Create → New Sale
2. Select customer ledger
3. Add items/services
4. Apply GST if applicable
5. Save invoice

### Viewing Reports
1. Navigate to Reports section
2. Select report type
3. Choose date range (Today/Week/Month/Year/Lifetime)
4. Export as PDF/Excel

## 🔒 Security

This application implements enterprise-level security:
- ✅ Password hashing with bcrypt
- ✅ CSRF token protection
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ Session security with timeout
- ✅ IP-based session validation
- ✅ Secure headers

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👨‍💻 Developer

**Mahadev Mondal**
- Website: [fullstackpro.tech](https://fullstackpro.tech)
- Email: support@codteg.com
- GitHub: [@mahadebmondal004](https://github.com/mahadebmondal004)

## 🙏 Acknowledgments

- Bootstrap team for the amazing framework
- Font Awesome for beautiful icons
- Chart.js for interactive charts
- All contributors and users

## 📞 Support

For support, email support@codteg.com or visit [fullstackpro.tech](https://fullstackpro.tech)

---

**Made with ❤️ by Mahadev**

⭐ Star this repository if you find it helpful!
