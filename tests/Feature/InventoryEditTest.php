<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\InventoryCategory;
use App\Models\InventorySubcategory;
use App\Models\ProductModel;
use App\Models\InventoryTransaction;
use App\Models\InventoryStock;
use App\Models\InventoryLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryEditTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;
    protected $admin;
    protected $warehouse;
    protected $category;
    protected $subcategory;
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard Roles in database manually since we are using RefreshDatabase
        \DB::table('roles')->insert([
            ['id' => 1, 'name' => 'super-admin', 'description' => 'Super Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'admin', 'description' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'employee', 'description' => 'Employee', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
            'warehouse_id' => null,
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Warehouse Alpha',
            'location' => 'Location Alpha',
            'address' => 'Address Alpha',
            'contact_number' => '1234567890',
            'email' => 'alpha@warehouse.com',
            'status' => 'active',
            'created_by' => $this->superAdmin->id,
        ]);

        $this->admin = User::create([
            'name' => 'Warehouse Admin',
            'email' => 'admin@warehouse.com',
            'password' => bcrypt('password'),
            'role_id' => 2,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'active',
        ]);

        $this->category = InventoryCategory::create(['name' => 'Category A']);
        $this->subcategory = InventorySubcategory::create([
            'category_id' => $this->category->id,
            'name' => 'Subcategory A',
        ]);
        $this->model = ProductModel::create([
            'subcategory_id' => $this->subcategory->id,
            'model_name' => 'Model X',
        ]);
    }

    public function test_super_admin_can_edit_transaction_and_stock_is_adjusted()
    {
        // 1. Add some initial stock via transaction
        $transaction = InventoryTransaction::create([
            'model_id' => $this->model->id,
            'warehouse_id' => $this->warehouse->id,
            'qty' => 10,
            'type' => 'add',
            'transaction_subtype' => 'purchase_stock',
            'created_by' => $this->superAdmin->id,
        ]);

        $stock = InventoryStock::create([
            'model_id' => $this->model->id,
            'warehouse_id' => $this->warehouse->id,
            'total_stock' => 10,
            'available_stock' => 10,
            'created_by' => $this->superAdmin->id,
        ]);

        // 2. Perform edit to change quantity to 15
        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('inventory.transactions.update', $transaction->id), [
                'model_id' => $this->model->id,
                'warehouse_id' => $this->warehouse->id,
                'qty' => 15,
                'transaction_subtype' => 'purchase_stock',
                'remarks' => 'Corrected quantity',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 3. Verify stock is updated to 15
        $stock->refresh();
        $this->assertEquals(15, $stock->total_stock);
        $this->assertEquals(15, $stock->available_stock);

        // 4. Verify log was written
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_transaction_id' => $transaction->id,
            'user_id' => $this->superAdmin->id,
            'old_qty' => 10,
            'new_qty' => 15,
            'old_remarks' => null,
            'new_remarks' => 'Corrected quantity',
        ]);
    }

    public function test_edit_fails_if_stock_level_becomes_negative()
    {
        // 1. Add some initial stock
        $transaction = InventoryTransaction::create([
            'model_id' => $this->model->id,
            'warehouse_id' => $this->warehouse->id,
            'qty' => 10,
            'type' => 'add',
            'transaction_subtype' => 'purchase_stock',
            'created_by' => $this->superAdmin->id,
        ]);

        $stock = InventoryStock::create([
            'model_id' => $this->model->id,
            'warehouse_id' => $this->warehouse->id,
            'total_stock' => 10,
            'available_stock' => 5, // 5 are already allocated/sold
            'created_by' => $this->superAdmin->id,
        ]);

        // 2. Try to edit transaction quantity to 2 (deducting 8 from stock, but only 5 available)
        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('inventory.transactions.update', $transaction->id), [
                'model_id' => $this->model->id,
                'warehouse_id' => $this->warehouse->id,
                'qty' => 2,
                'transaction_subtype' => 'purchase_stock',
            ]);

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('Insufficient stock', $response->json('message'));

        // Stock and transaction should remain unchanged
        $stock->refresh();
        $this->assertEquals(10, $stock->total_stock);
        $this->assertEquals(5, $stock->available_stock);
    }
}
