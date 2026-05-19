<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_transaction_id')->constrained('inventory_transactions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('action'); // e.g., 'update'
            
            // Quantities
            $table->integer('old_qty')->nullable();
            $table->integer('new_qty');
            
            // Models
            $table->foreignId('old_model_id')->nullable()->constrained('models')->onDelete('set null');
            $table->foreignId('new_model_id')->constrained('models')->onDelete('restrict');
            
            // Warehouses
            $table->foreignId('old_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('new_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            
            // Transaction Subtypes
            $table->string('old_transaction_subtype')->nullable();
            $table->string('new_transaction_subtype')->nullable();
            
            // Invoice Details
            $table->string('old_invoice_no')->nullable();
            $table->string('new_invoice_no')->nullable();
            $table->date('old_invoice_date')->nullable();
            $table->date('new_invoice_date')->nullable();
            $table->string('old_invoice_path')->nullable();
            $table->string('new_invoice_path')->nullable();
            
            // Remarks
            $table->text('old_remarks')->nullable();
            $table->text('new_remarks')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
