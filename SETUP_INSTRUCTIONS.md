# 🚀 Warehouse Management System - Setup Instructions

## Quick Start

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Setup Environment
Copy `.env.example` to `.env` and configure:
- Database credentials
- APP_URL
- APP_NAME

### 3. Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

### 4. Create Storage Link
```bash
php artisan storage:link
```

### 5. Start Development Server
```bash
php artisan serve
```

## Default Login Credentials

- **Email:** master@admin.com
- **Password:** admin123
- **Role:** Super Admin

## Project Structure

- **Migrations:** Database tables created
- **Models:** All models with relationships
- **Controllers:** Auth, Dashboard, Warehouse, User, Inventory, Report
- **Middleware:** Role protection (SuperAdmin, Admin, AdminOrSuperAdmin)
- **Routes:** Unified routes with middleware protection
- **Views:** Basic structure with Bootstrap 5

## Next Steps

1. Complete remaining views (inventory/index.blade.php, warehouses/index.blade.php, etc.)
2. Add DataTables for inventory listing
3. Add Chart.js for reports
4. Complete AJAX functionality for CRUD operations
5. Add form validation

## Notes

- Export functionality removed as requested
- Custom Bootstrap 5 (no AdminLTE)
- Roles table with seeder
- 50MB max file upload
- Light/Dark theme support
- Unified controller/view system (DRY principle)

