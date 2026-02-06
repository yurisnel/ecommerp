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
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_entry_id')->nullable()->constrained()->onDelete('set null'); // Track which batch was sold
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2); // Selling price at time of sale
            $table->decimal('unit_cost', 10, 2)->nullable(); // Cost for profit calculation
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2); // quantity * unit_price
            $table->decimal('total', 12, 2); // subtotal - discount + tax
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
