<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('models')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->integer('qty');
            $table->enum('type', ['add', 'deduct', 'transfer']);
            $table->string('invoice_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->text('remarks')->nullable();
            $table->foreignId('transfer_from_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('transfer_to_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->timestamps();

            $table->index('warehouse_id');
            $table->index('created_at');
            $table->index(['warehouse_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
