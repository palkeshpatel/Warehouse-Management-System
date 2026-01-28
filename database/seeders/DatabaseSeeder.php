<?php

namespace Database\Seeders;

use App\Models\InventoryCategory;
use App\Models\InventorySubcategory;
use App\Models\ProductModel;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Roles
        $this->call(RoleSeeder::class);

        // Seed Return Reasons
        $this->call(SalesReturnReasonSeeder::class);
        $this->call(PurchaseReturnReasonSeeder::class);

        // Create Master Admin (Super Admin)
        User::create([
            'name' => 'Master Admin',
            'email' => 'master@admin.com',
            'password' => Hash::make('admin123'),
            'role_id' => 1,
            'warehouse_id' => null,
            'theme_preference' => 'light',
            'status' => 'active',
        ]);

        // Create 6 Warehouses
        $warehouses = [];
        for ($i = 1; $i <= 6; $i++) {
            $warehouses[] = Warehouse::create([
                'name' => 'Warehouse ' . $i,
                'location' => 'Location ' . $i,
                'address' => 'Address for Warehouse ' . $i,
                'contact_number' => '987654321' . $i,
                'email' => 'warehouse' . $i . '@example.com',
                'status' => 'active',
                'created_by' => 1,
            ]);
        }

        // Create 1 Admin for each warehouse (6 Admins)
        foreach ($warehouses as $index => $warehouse) {
            User::create([
                'name' => 'Admin Warehouse ' . ($index + 1),
                'email' => 'admin' . ($index + 1) . '@warehouse.com',
                'password' => Hash::make('admin123'),
                'role_id' => 2,
                'warehouse_id' => $warehouse->id,
                'theme_preference' => 'light',
                'status' => 'active',
            ]);
        }

        // Create 1 Employee for each warehouse (6 Employees)
        foreach ($warehouses as $index => $warehouse) {
            User::create([
                'name' => 'Employee Warehouse ' . ($index + 1),
                'email' => 'employee' . ($index + 1) . '@warehouse.com',
                'password' => Hash::make('admin123'),
                'role_id' => 3,
                'warehouse_id' => $warehouse->id,
                'theme_preference' => 'light',
                'status' => 'active',
            ]);
        }

        // Create Inventory Categories
        $panelsCategory = InventoryCategory::create(['name' => 'Panels']);
        $inverterCategory = InventoryCategory::create(['name' => 'Inverter']);

        // Create Subcategories for Panels
        $adaniSolar = InventorySubcategory::create([
            'category_id' => $panelsCategory->id,
            'name' => 'Adani Solar'
        ]);

        // Create Subcategories for Inverter
        $sma = InventorySubcategory::create([
            'category_id' => $inverterCategory->id,
            'name' => 'SMA'
        ]);

        $jioSpark = InventorySubcategory::create([
            'category_id' => $inverterCategory->id,
            'name' => 'Jio Spark'
        ]);

        // Create Models for Adani Solar (Panels)
        $models = ['550', '560', '565', '570', '575', '580', '600', '610', '620'];
        foreach ($models as $modelName) {
            ProductModel::create([
                'subcategory_id' => $adaniSolar->id,
                'model_name' => $modelName
            ]);
        }

        // Create Models for sma (Inverter)
        $smaModels = ['3.0', '3.6', '4.0', '5.0', '6.0'];
        foreach ($smaModels as $modelName) {
            ProductModel::create([
                'subcategory_id' => $sma->id,
                'model_name' => $modelName
            ]);
        }

        // Create Model for Jio Spark (Inverter)
        ProductModel::create([
            'subcategory_id' => $jioSpark->id,
            'model_name' => '2.0 KW'
        ]);

        // No inventory stock or transaction records - fresh start!
        // inventory_stock and inventory_transactions tables will remain empty

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('=== Login Credentials ===');
        $this->command->info('');
        $this->command->info('Super Admin:');
        $this->command->info('  Email: master@admin.com');
        $this->command->info('  Password: admin123');
        $this->command->info('');
        $this->command->info('Admin Users (1 for each warehouse):');
        for ($i = 1; $i <= 6; $i++) {
            $this->command->info("  Admin $i: admin$i@warehouse.com / admin123");
        }
        $this->command->info('');
        $this->command->info('Employee Users (1 for each warehouse):');
        for ($i = 1; $i <= 6; $i++) {
            $this->command->info("  Employee $i: employee$i@warehouse.com / admin123");
        }
    }
}