<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super-admin', 'description' => 'Master Admin with full access'],
            ['name' => 'admin', 'description' => 'Warehouse Admin'],
            ['name' => 'employee', 'description' => 'Warehouse Employee'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}