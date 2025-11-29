<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('models')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->integer('total_stock')->default(0);
            $table->integer('available_stock')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['model_id', 'warehouse_id']);
            $table->index('warehouse_id');
            $table->index('model_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock');
    }
};
