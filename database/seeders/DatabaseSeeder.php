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
        $sima = InventorySubcategory::create([
            'category_id' => $inverterCategory->id,
            'name' => 'SIMA'
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

        // Create Models for SIMA (Inverter)
        $simaModels = ['3.0', '3.6', '4.0', '5.0', '6.0'];
        foreach ($simaModels as $modelName) {
            ProductModel::create([
                'subcategory_id' => $sima->id,
                'model_name' => $modelName
            ]);
        }

        // Create Model for Jio Spark (Inverter)
        ProductModel::create([
            'subcategory_id' => $jioSpark->id,
            'model_name' => '2.0 KW'
        ]);

        // Create Demo Inventory Stock Data
        $allWarehouses = Warehouse::all();

        // Get models by subcategory
        $adaniModels = ProductModel::where('subcategory_id', $adaniSolar->id)->get();
        $simaModels = ProductModel::where('subcategory_id', $sima->id)->get();
        $jioSparkModel = ProductModel::where('subcategory_id', $jioSpark->id)->first();

        // Add inventory to each warehouse
        foreach ($allWarehouses as $warehouse) {
            // Add some panel models (first 5 Adani models)
            $panelModelsToAdd = $adaniModels->take(5);
            foreach ($panelModelsToAdd as $model) {
                $qty = rand(50, 200);
                \App\Models\InventoryStock::create([
                    'model_id' => $model->id,
                    'warehouse_id' => $warehouse->id,
                    'total_stock' => $qty,
                    'available_stock' => $qty,
                    'created_by' => 1,
                ]);
            }

            // Add some inverter models (first 3 SIMA models)
            $inverterModelsToAdd = $simaModels->take(3);
            foreach ($inverterModelsToAdd as $model) {
                $qty = rand(20, 100);
                \App\Models\InventoryStock::create([
                    'model_id' => $model->id,
                    'warehouse_id' => $warehouse->id,
                    'total_stock' => $qty,
                    'available_stock' => $qty,
                    'created_by' => 1,
                ]);
            }

            // Add Jio Spark model
            if ($jioSparkModel) {
                $qty = rand(15, 80);
                \App\Models\InventoryStock::create([
                    'model_id' => $jioSparkModel->id,
                    'warehouse_id' => $warehouse->id,
                    'total_stock' => $qty,
                    'available_stock' => $qty,
                    'created_by' => 1,
                ]);
            }
        }

        // Create some transaction history
        $inventoryStocks = \App\Models\InventoryStock::all();
        foreach ($inventoryStocks->take(20) as $stock) {
            \App\Models\InventoryTransaction::create([
                'model_id' => $stock->model_id,
                'warehouse_id' => $stock->warehouse_id,
                'qty' => rand(10, 50),
                'type' => 'add',
                'created_by' => 1,
                'remarks' => 'Initial stock entry',
            ]);
        }

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
