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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipping_zone_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('calculation_type', ['fixed', 'weight_based', 'price_based', 'item_based']);
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->decimal('rate_per_unit', 10, 2)->nullable(); // Per kg, per item, per $ of order
            $table->decimal('min_order_amount', 10, 2)->nullable(); // Free shipping threshold
            $table->decimal('max_weight', 10, 2)->nullable();
            $table->boolean('is_free_shipping')->default(false);
            $table->timestamps();
            
            $table->unique(['shipping_method_id', 'shipping_zone_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
