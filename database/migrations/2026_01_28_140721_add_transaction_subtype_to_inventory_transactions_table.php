<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->enum('transaction_subtype', ['purchase_stock', 'sales_return', 'sales', 'purchase_return'])->nullable()->after('type');
            $table->foreignId('sales_return_reason_id')->nullable()->constrained('sales_return_reasons')->onDelete('set null')->after('transaction_subtype');
            $table->foreignId('purchase_return_reason_id')->nullable()->constrained('purchase_return_reasons')->onDelete('set null')->after('sales_return_reason_id');
            $table->text('reason_other')->nullable()->after('purchase_return_reason_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['sales_return_reason_id']);
            $table->dropForeign(['purchase_return_reason_id']);
            $table->dropColumn(['transaction_subtype', 'sales_return_reason_id', 'purchase_return_reason_id', 'reason_other']);
        });
    }
};
