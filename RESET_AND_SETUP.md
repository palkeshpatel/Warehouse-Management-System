# 🔄 Complete Reset & Setup Guide

## Step-by-Step Instructions

### Step 1: Reset Database (If Needed)

If you have existing tables and want to start fresh:

```bash
php artisan migrate:fresh
```

**OR** if you just want to rollback and re-run:

```bash
php artisan migrate:rollback --step=10
php artisan migrate
```

### Step 2: Run Migrations

```bash
php artisan migrate
```

This will create all tables in the correct order:
1. `roles` table
2. `warehouses` table  
3. `inventory_categories` table
4. `users` table (with foreign keys)
5. `inventory_subcategories` table
6. `models` table
7. `inventory_stock` table
8. `inventory_transactions` table

### Step 3: Seed Database with Demo Data

```bash
php artisan db:seed
```

This will create:
- ✅ 3 Roles (super-admin, admin, employee)
- ✅ 1 Super Admin user
- ✅ 2 Warehouses (Main & Branch)
- ✅ 2 Admin users (one per warehouse)
- ✅ 2 Employee users (one per warehouse)
- ✅ 2 Categories (Panels, Inverter)
- ✅ 3 Subcategories (Adani Solar, SIMA, Jio Spark)
- ✅ Multiple Models (550, 560, 565, 570, 575, 580, 600, 610, 620 for Panels)
- ✅ Inverter Models (3.0, 3.6, 4.0, 5.0, 6.0 for SIMA and 2.0 KW for Jio Spark)

### Step 4: Create Storage Link (For File Uploads)

```bash
php artisan storage:link
```

This creates a symbolic link from `storage/app/public` to `public/storage` for invoice uploads.

### Step 5: Clear Cache (If Needed)

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 6: Start Development Server

```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000`

## 📝 Demo Login Credentials

### Super Admin (Full Access)
- **Email:** master@admin.com
- **Password:** admin123
- **Access:** All warehouses, can manage everything

### Admin User 1 (Mumbai Warehouse)
- **Email:** admin1@warehouse.com
- **Password:** admin123
- **Access:** Only Main Warehouse (Mumbai)

### Admin User 2 (Delhi Warehouse)
- **Email:** admin2@warehouse.com
- **Password:** admin123
- **Access:** Only Branch Warehouse (Delhi)

### Employee User 1 (Mumbai Warehouse)
- **Email:** employee1@warehouse.com
- **Password:** admin123
- **Access:** Only Main Warehouse, can add/deduct inventory

### Employee User 2 (Delhi Warehouse)
- **Email:** employee2@warehouse.com
- **Password:** admin123
- **Access:** Only Branch Warehouse, can add/deduct inventory

## 🎯 Quick Test Steps

1. **Login as Super Admin:**
   - Go to `/login`
   - Login with: master@admin.com / admin123
   - You should see dashboard with all warehouses

2. **Test Warehouse Management:**
   - Click "Warehouses" in sidebar
   - Create/Edit/Delete warehouses

3. **Test User Management:**
   - Click "Users" in sidebar
   - Create new Admin/Employee users
   - Assign them to warehouses

4. **Test Inventory:**
   - Click "Inventory" in sidebar
   - Click "Add Inventory"
   - Select Category → Subcategory → Model
   - Enter quantity and save

5. **Test Reports:**
   - Click "Reports" in sidebar
   - Filter by warehouse and date range

## ⚠️ Troubleshooting

### Error: Table already exists
**Solution:** Run `php artisan migrate:fresh` to drop all tables and recreate them.

### Error: Foreign key constraint fails
**Solution:** Make sure migrations run in correct order. Check migration files don't have duplicates.

### Error: Storage link not working
**Solution:** Delete existing `public/storage` folder and run `php artisan storage:link` again.

### Error: Route not found
**Solution:** Run `php artisan route:clear` and `php artisan cache:clear`

## ✅ Verification Checklist

After setup, verify:
- [ ] Can login with super admin credentials
- [ ] Dashboard shows statistics
- [ ] Can access warehouses page (super admin only)
- [ ] Can access users page (super admin only)
- [ ] Can access inventory page (all users)
- [ ] Can add inventory
- [ ] Can deduct inventory (requires invoice)
- [ ] Can transfer stock (super admin only)
- [ ] Theme toggle works
- [ ] Sidebar navigation works

