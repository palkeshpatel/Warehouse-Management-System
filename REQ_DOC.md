we are Making
Laravel + Bootstrap admin panel
Fast+BEST+Light & stable
attractive admin panel and smooth
use ajax+jaquery valdation
prroject desription

📝 Warehouse-Management-System
Image 1 (image_195f81.jpg) - Inventory Report & Panels Category
Inventory Report

Options for:

Yearly

Quarterly

Monthly

Weekly

Daily Reports.

[Only Master Admin]

Inventory Lists

Category - Panels

Sub-Category - Adani Solar

DCR

TopCon

Bifacial

Non DCR

TopCon

Bifacial

Models (presumably for both TopCon & Bifacial): 550, 560, 565, 570, 575, 580, 600, 610, 620

Image 2 (image_195f1f.png) - Second Category & Invoice Attachment
Second Category - Invertor

Sub-Category - SIMA

3.0 / 3.6 / 4.0 / 5.0 / 6.0

Jio Spark

2.0 KW

There should be an attachment to attach Invoice JPG or PDF with the entry for Inventory deduction.

Image 3 (image_19629f.png) - URL & User Roles
Inventory URL Link:

Workflow

Currently use a Temporary Link for the URL to Start with.

Login Credentials

Master Admin - All Inventory Showcase + 100% Customisation Option.

Admin - All Inventory Showcase

Employees - They can Add or Deduct Inventory but don't have the rights. They can't see the total inventory.

📘 WAREHOUSE MANAGEMENT SYSTEM – DEVELOPMENT PLAN (Laravel + Bootstrap)
✅ 1. Project Overview

A Warehouse Inventory Management System built in Laravel, using:

Bootstrap 5 (Admin Panel UI)

**Light/Dark Theme Support** (Toggle switch - User preference saved)

jQuery + AJAX CRUD

Laravel Authentication (with roles)

Multi-level Inventory (Category → Subcategory → Models)

Master Admin customisation

Invoice upload (JPG/PDF)

Role-based dashboard views

Inventory reporting (daily/weekly/monthly/yearly)

Three roles:

super-admin (Master Admin)

admin

employee

✅ 1.1 Role System (Using Roles Table/Enum)

**How Roles Work:**

1. **Super Admin:**

    - `role = 'super-admin'` (from roles table or enum)
    - `warehouse_id = NULL` (not assigned to any warehouse)
    - Can create/manage warehouses
    - Can access all warehouses

2. **Admin (Warehouse-wise Admin):**

    - `role = 'admin'` (from roles table or enum)
    - `warehouse_id = REQUIRED` (assigned to ONE warehouse)
    - Account is ONLY for that specific warehouse
    - Cannot access other warehouses

3. **Employee (Warehouse-wise Employee):**
    - `role = 'employee'` (from roles table or enum)
    - `warehouse_id = REQUIRED` (assigned to ONE warehouse)
    - Account is ONLY for that specific warehouse
    - Cannot access other warehouses

**Role Check Logic:**

-   Check `role` field directly: `role = 'super-admin'` / `role = 'admin'` / `role = 'employee'`
-   Then check `warehouse_id`:
    -   Super Admin: `warehouse_id IS NULL`
    -   Admin/Employee: `warehouse_id IS NOT NULL`

✅ 2. User Role Capabilities

**Super Admin**

Full access to EVERYTHING

Manage Warehouses (Create/Edit/Delete)

Manage Admin + Employee users (with warehouse assignment)

Create/Update/Delete Inventory categories/subcategories/models

View ALL inventory (across all warehouses)

Warehouse selector/dropdown for filtering

Stock Transfer between warehouses

Upload/download invoices

Inventory full report (daily–yearly) - All warehouses / Single / Multiple

All customization allowed

**Admin**

Can view inventory ONLY from their assigned warehouse

Cannot create new users

Cannot change system settings

Cannot manage warehouses

Can only manage inventory (add/edit/deduct) - Auto-assigned to their warehouse

Can upload invoice

Can see reports ONLY for their warehouse

**Employee**

Can only add or deduct inventory - Auto-assigned to their warehouse

Cannot see totals

Cannot edit categories

Cannot see reports

Cannot manage warehouses

Cannot see other warehouses' inventory

✅ 2.1 Warehouse Management System

**Warehouse Master Features:**

-   Only Super Admin can create/edit/delete warehouses
-   Warehouse fields: name, location, address, contact_number, email (optional), status (active/inactive), created_by
-   Each warehouse maintains separate inventory stock
-   Same model can exist in multiple warehouses with different quantities

**Inventory Per Warehouse:**

-   Inventory is tracked per warehouse (warehouse_id is REQUIRED in inventory_stock table)
-   Example: Warehouse A has 120 units of Model 550, Warehouse B has 80 units of Model 550
-   Categories/Subcategories/Models are GLOBAL (shared across all warehouses)
-   Only inventory quantities differ per warehouse

**User-Warehouse Assignment:**

-   **Super Admin:** `role = 'super-admin'` AND `warehouse_id = NULL` (not assigned to any warehouse)
-   **Admin:** `role = 'admin'` AND `warehouse_id = REQUIRED` (assigned to ONE warehouse only)
-   **Employee:** `role = 'employee'` AND `warehouse_id = REQUIRED` (assigned to ONE warehouse only)
-   When Admin/Employee adds/deducts inventory, warehouse_id is automatically assigned from their user record
-   Super Admin can change Admin/Employee warehouse assignment anytime
-   **Role Check:** Check `role` field directly (from roles table or enum), then check warehouse_id

**Stock Transfer:**

-   Super Admin can transfer stock between warehouses
-   Creates 2 transaction entries: Deduct from Source + Add to Destination
-   Both transactions linked with transfer_from_warehouse_id and transfer_to_warehouse_id

**Reports & Dashboards:**

-   Super Admin: Can see all warehouses / single warehouse / multiple selected warehouses
-   Admin: Can see reports ONLY for their assigned warehouse
-   Employee: No reports access
-   Dashboard stats filtered by warehouse based on user role

✅ 3. Project Folder Structure (Laravel Best Practice)
app/
Http/
Controllers/
Auth/
DashboardController.php (Unified - adapts based on role)
WarehouseController.php (Only Super Admin)
UserController.php (Only Super Admin)
InventoryController.php (Unified - adapts based on role)
ReportController.php (Super Admin & Admin)
API/
Middleware/
Models/
resources/
views/
auth/
login.blade.php (Single unified login page)
dashboard.blade.php (Unified - shows different content based on role)
warehouses/ (Only Super Admin sees)
users/ (Only Super Admin sees)
inventory/ (All roles - adapts based on role)
reports/ (Super Admin & Admin)
layouts/
main.blade.php (Unified layout - sidebar adapts based on role)
public/
assets/
css/
js/
uploads/

✅ 4. Database Design (Migrations)

**4.1 Roles Table (Option 1 - Recommended)**
id
name (super-admin, admin, employee)
description
timestamps

**OR use ENUM in Users table (Option 2 - Simpler)**

**4.2 Users Table**
id
name
email
password
role_id (foreign key to roles table) OR role (enum: 'super-admin', 'admin', 'employee')
warehouse_id (NULL for super-admin, REQUIRED for admin/employee)
theme_preference (enum: 'light', 'dark' - default: 'light')
status
timestamps

**Role Assignment Rules:**

-   Super Admin: `role = 'super-admin'` AND `warehouse_id = NULL`
-   Admin: `role = 'admin'` AND `warehouse_id IS NOT NULL` (assigned to ONE warehouse)
-   Employee: `role = 'employee'` AND `warehouse_id IS NOT NULL` (assigned to ONE warehouse)

    4.3 Warehouses Table
    id
    name
    location
    address
    contact_number
    email (nullable)
    status (active/inactive)
    created_by (user id)
    timestamps

    4.4 Inventory Categories
    id
    name (Panels, Inverter)
    timestamps

    4.5 Inventory Subcategories
    id
    category_id
    name (Adani Solar, SIMA, Jio Spark)
    timestamps

    4.6 Models Table
    id
    subcategory_id
    model_name (550, 560, 3.0, 3.6 etc.)
    timestamps

    4.7 Inventory Stock Table
    id
    model_id
    warehouse_id (REQUIRED - links to warehouses table)
    total_stock
    available_stock
    created_by
    timestamps

    4.8 Inventory Transactions Table

For add/deduct actions:

id
model_id
warehouse_id (REQUIRED - links to warehouses table)
qty
type (add / deduct / transfer)
invoice_path (jpg/pdf)
created_by (user id)
remarks
transfer_from_warehouse_id (nullable - for transfer type)
transfer_to_warehouse_id (nullable - for transfer type)
timestamps

✅ 5. Laravel Routing Structure

**Authentication (Unified Login)**
/login (Single login page for all users)
/logout

**Theme Management**
/theme/toggle (AJAX - Toggle light/dark theme)
/user/update-theme (AJAX - Update user theme preference)

**After Login - Role-Based Redirection:**

-   All users → `/dashboard` (Same route, different content based on role)

**Unified Routes (Single Controller/View - Adapts Based on User Role)**

/dashboard (Shows different content based on role)
/warehouses (CRUD - Only Super Admin can access)
/warehouses/create (Only Super Admin)
/warehouses/edit/{id} (Only Super Admin)
/users (Only Super Admin can access)
/users/create (Only Super Admin - with warehouse assignment)
/inventory (All roles - filtered by warehouse based on role)
/inventory/add (All roles - auto-assigned warehouse for Admin/Employee)
/inventory/edit/{id} (Super Admin & Admin only)
/inventory/deduct (All roles - auto-assigned warehouse for Admin/Employee)
/inventory/transfer (Only Super Admin)
/reports (Super Admin & Admin - filtered by warehouse based on role)

**How It Works:**

-   Single route, single controller, single view
-   Controller checks user role and filters data accordingly
-   View shows/hides features based on role (using @if/@can directives)
-   Middleware protects routes (e.g., /warehouses only for super-admin)
-   No code duplication - DRY principle

✅ 6. Frontend Admin Panel UI (Bootstrap)

**Theme System:**

-   **Light/Dark Theme Toggle** (Required Feature)
-   Theme toggle switch in topbar/navbar
-   User preference saved in database (users table: theme_preference)
-   Theme persists across sessions
-   Smooth transition between themes
-   All components support both themes (cards, tables, modals, forms, charts)

**Layout includes:**

Sidebar (Modules) - Theme-aware

Topbar (Notifications, User Profile, Theme Toggle, Warehouse Selector for Super Admin) - Theme-aware

Dashboard Cards - Theme-aware styling

Inventory Statistics Graph (Chart.js) - Theme-aware colors

jQuery AJAX CRUD

Bootstrap modals for Add/Edit - Theme-aware

**Theme Implementation:**

-   CSS variables for colors (easy theme switching)
-   Dark mode: Dark backgrounds, light text
-   Light mode: Light backgrounds, dark text
-   All UI elements adapt to selected theme

**Dashboard KPIs:**

**Super Admin Dashboard:**

-   Total Inventory Count (All Warehouses)
-   Per-Warehouse Breakdown Cards
-   Today Added (All Warehouses)
-   Today Deducted (All Warehouses)
-   Total Categories
-   Total Warehouses
-   Low Stock Alerts (All Warehouses)
-   Warehouse-wise Charts

**Admin Dashboard:**

-   Total Inventory Count (Their Warehouse Only)
-   Today Added (Their Warehouse)
-   Today Deducted (Their Warehouse)
-   Low Stock Alerts (Their Warehouse)

**Employee Dashboard:**

-   Simple Add/Deduct Forms
-   No totals displayed
-   No statistics

✅ 7. AJAX + jQuery Development Flow

**7.1 Unified Login Flow**

Single login page: `/login` (for all users)

AJAX request to `/login`
**After successful login, system automatically redirects:**

-   All users → Redirect to `/dashboard` (Same route for everyone)
-   Controller checks user role and shows appropriate content
-   View adapts based on role (shows/hides features)

**Login Logic:**

1. User enters email/password on `/login` page
2. System authenticates user
3. System checks user's `role` field
4. System redirects to appropriate dashboard based on role
5. Middleware protects routes based on role

7.2 Warehouse Management (Super Admin Only)

List warehouses: /warehouses (Middleware protects - only Super Admin)

AJAX add warehouse: /warehouses (POST)

AJAX edit/delete warehouse: /warehouses/{id} (PUT/DELETE)

Warehouse status toggle (active/inactive)

7.3 User Management (Super Admin Only)

List users: /users (Middleware protects - only Super Admin)

AJAX add user: /users (POST - with REQUIRED warehouse assignment for Admin/Employee)

AJAX edit/delete user: /users/{id} (PUT/DELETE)

Change warehouse assignment for Admin/Employee

Super Admin users have NULL warehouse_id

7.4 Inventory CRUD (Unified Controller/View)

Routes: /inventory (GET, POST, PUT, DELETE)

Category → Subcategory → Model dropdown (dynamic with AJAX)

**Controller Logic (Single Controller):**

-   Check user role in controller
-   If Super Admin: Show warehouse selector, can select any warehouse
-   If Admin/Employee: Auto-assign warehouse_id from user.warehouse_id, hide warehouse selector

**View Logic (Single View):**

-   Use @if/@can directives to show/hide features
-   Super Admin sees: Warehouse selector, Edit button, Transfer button
-   Admin sees: No warehouse selector (auto-assigned), Edit button, No Transfer button
-   Employee sees: No warehouse selector (auto-assigned), No Edit button, No Transfer button

Add Inventory: /inventory (POST)

-   invoice upload (AJAX + FormData)
-   Controller checks role and assigns warehouse_id accordingly
-   Auto update stock in "inventory_stock" table (with warehouse_id)
-   Transaction log entry created (with warehouse_id)

Deduct Inventory: /inventory/deduct (POST)

-   invoice upload (REQUIRED for deduction)
-   Controller auto-assigns warehouse_id based on role
-   Auto update stock in "inventory_stock" table
-   Transaction log entry created

7.5 Stock Transfer (Super Admin Only)

Route: /inventory/transfer (POST)

Middleware protects route - only Super Admin can access

Transfer from Warehouse A → Warehouse B

-   Select source warehouse
-   Select destination warehouse
-   Select model
-   Enter quantity
-   Creates 2 transaction entries:
    -   Deduct from Warehouse A
    -   Add to Warehouse B
-   Both linked with transfer_from_warehouse_id and transfer_to_warehouse_id

    7.6 Inventory Reports

**Super Admin Reports:**

Report types:

-   Yearly
-   Quarterly
-   Monthly
-   Weekly
-   Daily

Warehouse filters:

-   All Warehouses (Combined)
-   Single Warehouse
-   Multiple Selected Warehouses

Using Chart.js + table export.

**Admin Reports:**

Same report types but ONLY for their assigned warehouse.

**Employee:**

No reports access.

✅ 8. Development Steps (Full Plan)
Step 1 – Setup Project

✔ Install Laravel
✔ Composer dependencies
✔ Auth scaffolding
✔ Middleware for roles
✔ Admin LTE / Bootstrap template
✔ Light/Dark theme system setup (CSS variables, toggle switch, theme persistence)

Step 2 – Create Migrations & Models

✔ roles (id, name: super-admin/admin/employee, description)
✔ users (with role_id foreign key OR role enum, warehouse_id - nullable for super-admin, required for admin/employee)
✔ warehouses (with full fields: name, location, address, contact_number, email, status, created_by)
✔ categories (GLOBAL - shared across all warehouses)
✔ subcategories (GLOBAL - shared across all warehouses)
✔ models (GLOBAL - shared across all warehouses)
✔ inventory_stock (with warehouse_id - REQUIRED)
✔ inventory_transactions (with warehouse_id - REQUIRED, transfer fields for stock transfer)

Step 3 – Seed Roles & Master Admin

**Seed Roles Table:**

1. super-admin
2. admin
3. employee

**Seed Master Admin:**
email: master@admin.com
password: admin123
role_id: 1 (or role: 'super-admin' if using enum)
warehouse_id: NULL

Step 4 – Authentication + Role Middleware

**Unified Login System:**

-   Single login page: `/login` (for all users)
-   After login, all users redirect to `/dashboard`
-   Controller automatically detects role and filters data
-   View automatically shows/hides features based on role

**Role Check Logic (Using Roles Table/Enum):**

isSuperAdmin() {
return auth()->user()->role === 'super-admin' && auth()->user()->warehouse_id === NULL;
}

isAdmin() {
return auth()->user()->role === 'admin' && auth()->user()->warehouse_id !== NULL;
}

isEmployee() {
return auth()->user()->role === 'employee' && auth()->user()->warehouse_id !== NULL;
}

**Alternative (if using role_id foreign key):**
isSuperAdmin() {
return auth()->user()->role->name === 'super-admin' && auth()->user()->warehouse_id === NULL;
}

**Middleware:**

-   Create middleware to protect routes based on role
-   Redirect unauthorized users to `/login`
-   Restrict URLs based on role checks

Step 5 – Build Unified Admin Panel (Single Controller/View System)

**Unified Dashboard Controller:**
✔ Dashboard (adapts based on role - shows different KPIs)

-   Super Admin: All warehouses + Per-warehouse breakdown
-   Admin: Only their warehouse stats
-   Employee: Simple forms only

**Unified Inventory Controller:**
✔ Inventory CRUD (adapts based on role)

-   Super Admin: Warehouse selector, Edit, Transfer buttons visible
-   Admin: Auto-assigned warehouse, Edit button visible, No Transfer
-   Employee: Auto-assigned warehouse, No Edit, No Transfer

**Unified Views with Conditional Rendering:**

-   Use @if/@can directives to show/hide features
-   Single view file, different content based on role
-   No code duplication

**Super Admin Only Modules (Middleware Protected):**
✔ Warehouse Management (CRUD)
✔ User management (CRUD with warehouse assignment)
✔ Inventory setup modules:

-   Category CRUD (GLOBAL)
-   Sub-Category CRUD (GLOBAL)
-   Model CRUD (GLOBAL)
    ✔ Stock Transfer Module (Warehouse A → Warehouse B)

**Admin & Super Admin:**
✔ Reports (filtered by warehouse based on role)

**All Roles:**
✔ Add Inventory (auto-assigned warehouse for Admin/Employee)
✔ Deduct Inventory (auto-assigned warehouse for Admin/Employee)

**Key Principle:**

-   Single controller, single view per module
-   Controller checks role and filters data
-   View shows/hides features using Blade directives (@if, @can)
-   Middleware protects routes
-   No separate controllers/views for each role - DRY principle

Step 8 – AJAX & jQuery Integration

**Theme Toggle Functionality:**

-   Theme toggle switch in topbar (Light/Dark)
-   AJAX request to `/theme/toggle` or `/user/update-theme`
-   Update user's `theme_preference` in database
-   Apply theme instantly without page reload
-   Save preference for future sessions
-   All components update dynamically (CSS variables)

All CRUD operations:

Add user

Add inventory

Edit inventory

Delete inventory

File upload

Theme toggle (Light/Dark)

Step 9 – Invoice Upload (PDF/JPG)

Upload inside:

public/uploads/invoices/

Store filename in inventory_transactions.invoice_path

Step 10 – Reporting Module

**Super Admin Reports:**

Reports by:

-   Daily
-   Weekly
-   Monthly
-   Quarterly
-   Yearly

Warehouse filters:

-   All Warehouses (Combined)
-   Single Warehouse
-   Multiple Selected Warehouses

Use:

-   Chart.js for graphs (per-warehouse charts)
-   Export to PDF/Excel

**Admin Reports:**

Same report types but ONLY for their assigned warehouse.

**Employee:**

No reports access.

Step 11 – Additional Features

✔ Low Stock Alerts (per warehouse)

✔ Download Invoice (PDF/JPG) from transactions

✔ Activity Logs (track all user actions)

✔ Bulk Upload Inventory (Excel/CSV for initial stock)

Step 12 – Testing

Role redirection

Warehouse assignment validation

Inventory math (per warehouse)

Stock transfer logic

File uploads

Validation

Permission restrictions

Warehouse filtering (Admin/Employee see only their warehouse)

Super Admin multi-warehouse access

🎯 Warehouse Management Summary

**Key Points:**

1. **Role System (Using Roles Table/Enum):**

    - **Super Admin:** role = 'super-admin' AND warehouse_id = NULL
    - **Admin:** role = 'admin' AND warehouse_id = NOT NULL (warehouse-specific admin)
    - **Employee:** role = 'employee' AND warehouse_id = NOT NULL (warehouse-specific employee)

2. **Warehouses are Master Data** - Only Super Admin (role = 'super-admin') can manage

3. **Inventory is Warehouse-Specific** - Each warehouse has its own stock for same models

4. **Categories/Subcategories/Models are GLOBAL** - Shared across all warehouses

5. **User-Warehouse Assignment:**

    - Super Admin: warehouse_id = NULL (can access all warehouses)
    - Admin: warehouse_id = REQUIRED (ONE warehouse only)
    - Employee: warehouse_id = REQUIRED (ONE warehouse only)

6. **Access Control:**

    - Super Admin: See ALL warehouses
    - Admin: See ONLY their assigned warehouse
    - Employee: See ONLY their assigned warehouse

7. **Stock Transfer:** Super Admin can transfer stock between warehouses

8. **Reports:** Filterable by warehouse (Super Admin can see all/single/multiple)

🎯 Unified Architecture Principle

**Key Concept: Single Controller/View System**

Instead of creating separate routes/controllers/views for each role, we use:

1. **Unified Routes:** Same route for all users (e.g., `/dashboard`, `/inventory`)
2. **Unified Controllers:** Single controller checks user role and filters data accordingly
3. **Unified Views:** Single view file uses Blade directives (@if, @can) to show/hide features
4. **Middleware Protection:** Routes protected by middleware (e.g., `/warehouses` only for Super Admin)

**Benefits:**

-   ✅ No code duplication (DRY principle)
-   ✅ Easier maintenance (one place to update)
-   ✅ Consistent UI/UX across roles
-   ✅ Less files to manage

**Example:**

-   Route: `/inventory` (same for all)
-   Controller: `InventoryController@index` - checks role, filters data
-   View: `inventory/index.blade.php` - uses @if to show/hide features
-   Super Admin sees: Warehouse selector, Edit button, Transfer button
-   Admin sees: No warehouse selector, Edit button, No Transfer button
-   Employee sees: No warehouse selector, No Edit button, No Transfer button

🎉 Final Summary

You now have a complete technical development plan including:

✔ DB design (with warehouse_id in stock & transactions)
✔ Warehouse Master Management
✔ Routes (with warehouse management)
✔ Modules
✔ Role logic (with warehouse restrictions)
✔ UI structure (warehouse-aware dashboards)
✔ **Light/Dark Theme System** (Toggle switch, user preference saved)
✔ AJAX flow (warehouse assignment, theme toggle)
✔ Reporting (warehouse-filtered)
✔ Invoice upload
✔ Stock Transfer Module
✔ Step-by-step development cycle

**Feature Matrix:**

| Feature                      | Super Admin | Admin  | Employee |
| ---------------------------- | ----------- | ------ | -------- |
| Manage Warehouses            | ✅ YES      | ❌ NO  | ❌ NO    |
| Manage Users                 | ✅ YES      | ❌ NO  | ❌ NO    |
| See all warehouses           | ✅ YES      | ❌ NO  | ❌ NO    |
| See only assigned warehouse  | ❌ NO       | ✅ YES | ✅ YES   |
| Add inventory                | ✅ YES      | ✅ YES | ✅ YES   |
| Deduct inventory             | ✅ YES      | ✅ YES | ✅ YES   |
| Manage categories            | ✅ YES      | ❌ NO  | ❌ NO    |
| Manage models                | ✅ YES      | ❌ NO  | ❌ NO    |
| Reports all warehouses       | ✅ YES      | ❌ NO  | ❌ NO    |
| Reports only their warehouse | ✅ YES      | ✅ YES | ❌ NO    |
| Stock transfer               | ✅ YES      | ❌ NO  | ❌ NO    |

If you want:
📌 PHP Controller code
📌 Routes/web.php full file
📌 Migrations for all tables (with warehouse_id)
📌 Blade files for dashboard
📌 Warehouse Management Module

Just tell me "Generate code" and I will generate everything.
