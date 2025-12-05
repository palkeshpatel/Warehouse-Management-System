# 📦 Warehouse Management System

A comprehensive Laravel-based Warehouse Management System for managing inventory across multiple warehouses with role-based access control.

## 📋 Table of Contents

-   [Project Overview](#project-overview)
-   [Technology Stack](#technology-stack)
-   [Features](#features)
-   [Installation](#installation)
-   [User Credentials](#user-credentials)
-   [Project Structure](#project-structure)
-   [Database Schema](#database-schema)
-   [Usage Guide](#usage-guide)
-   [API Routes](#api-routes)

---

## 🎯 Project Overview

This Warehouse Management System is designed to manage inventory across multiple warehouses with a hierarchical role-based access system. The system supports:

-   **Multi-warehouse management** - Manage inventory across 6 warehouses
-   **Role-based access control** - Three user roles with different permissions
-   **Inventory tracking** - Track stock levels, transactions, and transfers
-   **Category management** - Organize products by categories, subcategories, and models
-   **Transaction history** - Complete audit trail of all inventory movements
-   **Reporting** - Generate reports filtered by warehouse and date range

---

## 🛠 Technology Stack

-   **Framework:** Laravel 12.0
-   **PHP Version:** ^8.2
-   **Database:** MySQL/MariaDB (configurable)
-   **Frontend:** Bootstrap 5, Blade Templates
-   **Testing:** Pest PHP
-   **Package Manager:** Composer, NPM

### Key Dependencies

-   Laravel Framework 12.0
-   Laravel Tinker 2.10.1
-   Pest PHP 3.8 (Testing)
-   Bootstrap 5 (Frontend)

---

## ✨ Features

### 1. **User Management**

-   Three role types: Super Admin, Admin, Employee
-   User assignment to specific warehouses
-   Theme preference (Light/Dark mode)
-   User status management (Active/Inactive)

### 2. **Warehouse Management**

-   Create, edit, and manage multiple warehouses
-   Warehouse details: name, location, address, contact
-   Warehouse status management
-   Only Super Admin can manage warehouses

### 3. **Inventory Management**

-   **Categories:** Organize products (e.g., Panels, Inverter)
-   **Subcategories:** Further classification (e.g., Adani Solar, SIMA, Jio Spark)
-   **Models:** Specific product models (e.g., 550, 560, 3.0 KW, etc.)
-   **Stock Management:** Add, deduct, and transfer inventory
-   **Transaction Tracking:** Complete history of all inventory movements

### 4. **Inventory Operations**

-   **Add Stock:** Increase inventory levels
-   **Deduct Stock:** Decrease inventory (requires invoice upload)
-   **Transfer Stock:** Move inventory between warehouses (Admin/Super Admin only)
-   **Real-time Stock Tracking:** Available and total stock levels

### 5. **Reports & Analytics**

-   Transaction reports filtered by warehouse
-   Date range filtering
-   Accessible to all authenticated users

### 6. **Security & Access Control**

-   Role-based middleware protection
-   Warehouse-specific access for Admin and Employee roles
-   Super Admin has full system access

---

## 🚀 Installation

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   Node.js and NPM
-   MySQL/MariaDB database
-   XAMPP/WAMP (for local development)

### Step 1: Clone/Download Project

```bash
cd "D:\xampp\htdocs\clients\arun\Warehouse Management System"
```

### Step 2: Install Dependencies

```bash
composer install
npm install
```

### Step 3: Environment Configuration

1. Copy `.env.example` to `.env` (if not exists)
2. Generate application key:
    ```bash
    php artisan key:generate
    ```
3. Configure database in `.env`:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=warehouse_management
    DB_USERNAME=root
    DB_PASSWORD=
    ```

### Step 4: Database Setup

```bash
# Run migrations
php artisan migrate

# Seed database with demo data
php artisan db:seed
```

### Step 5: Create Storage Link

```bash
php artisan storage:link
```

### Step 6: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 7: Start Development Server

```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000`

---

## 👥 User Credentials

### 🔑 Super Admin (Full System Access)

**Email:** `master@admin.com`  
**Password:** `admin123`  
**Role:** Super Admin  
**Access Level:**

-   Manage all warehouses
-   Manage all users
-   Manage master data (Categories, Subcategories, Models)
-   View all inventory across all warehouses
-   Transfer stock between any warehouses
-   Full system access

---

### 🏢 Admin Users (Warehouse-Specific Access)

Each Admin user is assigned to a specific warehouse and can manage that warehouse's inventory.

#### Admin 1 - Warehouse 1

-   **Email:** `admin1@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 1
-   **Access:** Can manage inventory for Warehouse 1 only

#### Admin 2 - Warehouse 2

-   **Email:** `admin2@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 2
-   **Access:** Can manage inventory for Warehouse 2 only

#### Admin 3 - Warehouse 3

-   **Email:** `admin3@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 3
-   **Access:** Can manage inventory for Warehouse 3 only

#### Admin 4 - Warehouse 4

-   **Email:** `admin4@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 4
-   **Access:** Can manage inventory for Warehouse 4 only

#### Admin 5 - Warehouse 5

-   **Email:** `admin5@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 5
-   **Access:** Can manage inventory for Warehouse 5 only

#### Admin 6 - Warehouse 6

-   **Email:** `admin6@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 6
-   **Access:** Can manage inventory for Warehouse 6 only

**Admin Permissions:**

-   View and manage inventory for assigned warehouse
-   Add stock to assigned warehouse
-   Deduct stock from assigned warehouse
-   Transfer stock (to/from assigned warehouse)
-   View reports for assigned warehouse
-   Cannot manage warehouses or users
-   Cannot manage master data

---

### 👷 Employee Users (Warehouse-Specific Access)

Each Employee user is assigned to a specific warehouse and can perform basic inventory operations.

#### Employee 1 - Warehouse 1

-   **Email:** `employee1@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 1
-   **Access:** Can add/deduct inventory for Warehouse 1 only

#### Employee 2 - Warehouse 2

-   **Email:** `employee2@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 2
-   **Access:** Can add/deduct inventory for Warehouse 2 only

#### Employee 3 - Warehouse 3

-   **Email:** `employee3@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 3
-   **Access:** Can add/deduct inventory for Warehouse 3 only

#### Employee 4 - Warehouse 4

-   **Email:** `employee4@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 4
-   **Access:** Can add/deduct inventory for Warehouse 4 only

#### Employee 5 - Warehouse 5

-   **Email:** `employee5@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 5
-   **Access:** Can add/deduct inventory for Warehouse 5 only

#### Employee 6 - Warehouse 6

-   **Email:** `employee6@warehouse.com`
-   **Password:** `admin123`
-   **Warehouse:** Warehouse 6
-   **Access:** Can add/deduct inventory for Warehouse 6 only

**Employee Permissions:**

-   View inventory for assigned warehouse
-   Add stock to assigned warehouse
-   Deduct stock from assigned warehouse (requires invoice)
-   View reports for assigned warehouse
-   Cannot transfer stock
-   Cannot manage warehouses, users, or master data

---

## 📁 Project Structure

```
Warehouse Management System/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # Authentication
│   │   │   ├── DashboardController.php     # Dashboard
│   │   │   ├── InventoryController.php     # Inventory operations
│   │   │   ├── ReportController.php        # Reports
│   │   │   ├── UserController.php          # User management
│   │   │   ├── WarehouseController.php     # Warehouse management
│   │   │   └── Master/                     # Master data controllers
│   │   │       ├── CategoryController.php
│   │   │       ├── SubcategoryController.php
│   │   │       ├── ModelController.php
│   │   │       └── MasterController.php
│   │   └── Middleware/
│   │       ├── IsSuperAdmin.php            # Super Admin middleware
│   │       └── IsAdminOrSuperAdmin.php     # Admin/Super Admin middleware
│   └── Models/
│       ├── User.php
│       ├── Role.php
│       ├── Warehouse.php
│       ├── InventoryCategory.php
│       ├── InventorySubcategory.php
│       ├── ProductModel.php
│       ├── InventoryStock.php
│       └── InventoryTransaction.php
├── database/
│   ├── migrations/                          # Database migrations
│   └── seeders/
│       ├── DatabaseSeeder.php               # Main seeder
│       └── RoleSeeder.php                   # Role seeder
├── resources/
│   └── views/
│       ├── auth/                            # Authentication views
│       ├── dashboard.blade.php              # Dashboard
│       ├── inventory/                      # Inventory views
│       ├── masters/                        # Master data views
│       ├── reports/                        # Report views
│       ├── users/                          # User management views
│       ├── warehouses/                     # Warehouse views
│       └── layouts/                        # Layout templates
├── routes/
│   └── web.php                             # Application routes
├── config/                                  # Configuration files
├── public/                                  # Public assets
└── storage/                                 # Storage for uploads
```

---

## 🗄 Database Schema

### Core Tables

1. **roles** - User roles (super-admin, admin, employee)
2. **users** - User accounts with role and warehouse assignment
3. **warehouses** - Warehouse information
4. **inventory_categories** - Product categories (Panels, Inverter)
5. **inventory_subcategories** - Subcategories (Adani Solar, SIMA, Jio Spark)
6. **models** - Product models (550, 560, 3.0 KW, etc.)
7. **inventory_stock** - Current stock levels per warehouse
8. **inventory_transactions** - Transaction history

### Relationships

-   Users → Roles (Many-to-One)
-   Users → Warehouses (Many-to-One, nullable for Super Admin)
-   Categories → Subcategories (One-to-Many)
-   Subcategories → Models (One-to-Many)
-   Models → Inventory Stock (One-to-Many)
-   Warehouses → Inventory Stock (One-to-Many)
-   Inventory Transactions → Models, Warehouses, Users

---

## 📖 Usage Guide

### Login

1. Navigate to `http://127.0.0.1:8000/login`
2. Enter your credentials (see [User Credentials](#user-credentials))
3. Click "Login"

### Dashboard

After login, you'll see:

-   Total warehouses count
-   Total users count
-   Total inventory items
-   Recent transactions

### Managing Inventory

1. **Add Stock:**

    - Go to Inventory → Add Inventory
    - Select Category → Subcategory → Model
    - Enter quantity
    - Click "Add Stock"

2. **Deduct Stock:**

    - Go to Inventory → Deduct Inventory
    - Select Model
    - Enter quantity
    - Upload invoice (required)
    - Click "Deduct Stock"

3. **Transfer Stock:**
    - Go to Inventory → Transfer Stock (Admin/Super Admin only)
    - Select source and destination warehouses
    - Select model and quantity
    - Click "Transfer"

### Managing Warehouses (Super Admin Only)

1. Go to Warehouses
2. Click "Add Warehouse"
3. Fill in details (name, location, address, contact)
4. Save

### Managing Users (Super Admin Only)

1. Go to Users
2. Click "Add User"
3. Fill in details (name, email, password, role, warehouse)
4. Save

### Managing Master Data (Super Admin Only)

1. Go to Masters
2. Manage Categories, Subcategories, and Models
3. Create hierarchical structure: Category → Subcategory → Model

### Reports

1. Go to Reports
2. Select warehouse (if applicable)
3. Select date range
4. View transaction history

---

## 🛣 API Routes

### Authentication Routes

-   `GET /login` - Show login form
-   `POST /login` - Process login
-   `POST /logout` - Logout user

### Protected Routes (Requires Authentication)

#### Dashboard

-   `GET /dashboard` - Dashboard view

#### Warehouse Management (Super Admin Only)

-   `GET /warehouses` - List warehouses
-   `POST /warehouses` - Create warehouse
-   `GET /warehouses/{id}` - Show warehouse
-   `PUT /warehouses/{id}` - Update warehouse
-   `DELETE /warehouses/{id}` - Delete warehouse

#### User Management (Super Admin Only)

-   `GET /users` - List users
-   `POST /users` - Create user
-   `GET /users/{id}` - Show user
-   `PUT /users/{id}` - Update user
-   `DELETE /users/{id}` - Delete user

#### Master Data (Super Admin Only)

-   `GET /masters` - Master data index
-   `GET /masters/categories` - List categories
-   `POST /masters/categories` - Create category
-   `GET /masters/subcategories` - List subcategories
-   `POST /masters/subcategories` - Create subcategory
-   `GET /masters/models` - List models
-   `POST /masters/models` - Create model

#### Inventory (All Authenticated Users)

-   `GET /inventory` - List inventory
-   `POST /inventory` - Add stock
-   `POST /inventory/deduct` - Deduct stock
-   `GET /inventory/subcategories/{categoryId}` - Get subcategories
-   `GET /inventory/models/{subcategoryId}` - Get models
-   `GET /inventory/available-stock` - Get available stock

#### Stock Transfer (Admin/Super Admin Only)

-   `POST /inventory/transfer` - Transfer stock between warehouses

#### Reports (All Authenticated Users)

-   `GET /reports` - View reports
-   `POST /reports/filter` - Filter reports

---

## 🔒 Security Features

-   **Password Hashing:** All passwords are hashed using bcrypt
-   **CSRF Protection:** Laravel's built-in CSRF protection
-   **Middleware Protection:** Role-based route protection
-   **Warehouse Isolation:** Users can only access their assigned warehouse data
-   **File Upload Security:** Invoice uploads with validation

---

## 📝 Notes

-   **Default Password:** All demo users have password `admin123` - **Change in production!**
-   **File Upload Limit:** Maximum 50MB for invoice uploads
-   **Theme Support:** Light/Dark theme toggle available
-   **Export Functionality:** Removed as per requirements
-   **Framework:** Custom Bootstrap 5 (no AdminLTE)
-   **Database:** Supports MySQL, MariaDB, PostgreSQL, SQLite

---

## 🐛 Troubleshooting

### Error: Table already exists

**Solution:** Run `php artisan migrate:fresh` to drop all tables and recreate them.

### Error: Foreign key constraint fails

**Solution:** Ensure migrations run in correct order. Check migration files for duplicates.

### Error: Storage link not working

**Solution:** Delete existing `public/storage` folder and run `php artisan storage:link` again.

### Error: Route not found

**Solution:** Run `php artisan route:clear` and `php artisan cache:clear`

### Error: Class not found

**Solution:** Run `composer dump-autoload`

---

## 📞 Support

For issues or questions, please refer to:

-   `SETUP_INSTRUCTIONS.md` - Detailed setup guide
-   `RESET_AND_SETUP.md` - Reset and setup instructions
-   `REQ_DOC.md` - Requirements documentation

---

## 📄 License

This project is licensed under the MIT License.

---

## ✅ Verification Checklist

After setup, verify:

-   [ ] Can login with super admin credentials
-   [ ] Dashboard shows statistics
-   [ ] Can access warehouses page (super admin only)
-   [ ] Can access users page (super admin only)
-   [ ] Can access inventory page (all users)
-   [ ] Can add inventory
-   [ ] Can deduct inventory (requires invoice)
-   [ ] Can transfer stock (super admin/admin only)
-   [ ] Theme toggle works
-   [ ] Sidebar navigation works
-   [ ] Reports are accessible
-   [ ] Warehouse-specific access works correctly

---

**Last Updated:** 2024  
**Version:** 1.0.0  
**Framework:** Laravel 12.0
