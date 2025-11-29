<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained('roles')->onDelete('restrict');
            $table->foreignId('warehouse_id')->nullable()->after('role_id')->constrained('warehouses')->onDelete('restrict');
            $table->enum('theme_preference', ['light', 'dark'])->default('light')->after('warehouse_id');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('theme_preference');
            $table->index('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
            }
            if (Schema::hasColumn('users', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropIndex(['warehouse_id']);
            }
            $table->dropColumn(['role_id', 'warehouse_id', 'theme_preference', 'status']);
        });
    }
};
