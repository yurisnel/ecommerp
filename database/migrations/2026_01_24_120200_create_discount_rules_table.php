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
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('code', 100)->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed']); // percentage or fixed amount
            $table->decimal('value', 10, 2); // Discount value (percentage or amount)
            $table->foreignId('customer_group_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('applies_to', ['all', 'products', 'categories'])->default('all');
            $table->decimal('min_quantity', 10, 2)->nullable(); // Minimum quantity required
            $table->decimal('min_amount', 12, 2)->nullable(); // Minimum order amount required
            $table->decimal('max_discount', 12, 2)->nullable(); // Maximum discount amount
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('priority')->default(0); // Higher priority = applied first
            $table->boolean('combinable')->default(false); // Can be combined with other discounts
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};
