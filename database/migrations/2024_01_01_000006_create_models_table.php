<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcategory_id')->constrained('inventory_subcategories')->onDelete('cascade');
            $table->string('model_name');
            $table->timestamps();

            $table->unique(['subcategory_id', 'model_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('models');
    }
};
