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
        Schema::create('product_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->default(0)->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->decimal('base_cost', 10, 2); // Cost from supplier without additional costs
            $table->decimal('additional_costs_value', 10, 2)->default(0);
            $table->decimal('additional_costs_percent', 5, 2)->default(0);
            $table->decimal('unit_cost', 10, 2)->nullable(); // Calculated cost per unit including additional costs
            $table->decimal('unit_price', 10, 2); // Selling price for this batch             
            $table->date('expiration_date')->nullable();
            $table->timestamp('entry_date')->useCurrent();
            $table->string('batch_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_entries');
    }
};
