<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update existing data to match new enum values
        DB::table('products')->where('unit', 'pcs')->update(['unit' => 'u']);

        // 2. Change column to ENUM
        Schema::table('products', function (Blueprint $table) {
            // Note: DB::statement might be needed for ENUMs in some drivers, 
            // but Schema builder usually handles it if dbal is present.
            // Using explicit change for MySQL.
            $table->enum('unit', ['u', 'kg', 'm', 'l', 'box'])->default('u')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit', 50)->default('unit')->change();
        });
    }
};
