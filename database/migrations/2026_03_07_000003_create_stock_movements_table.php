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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();          
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->default(0)->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['in', 'out', 'transfer', 'adjustment']); // in=entrada, out=salida, transfer=transferencia, adjustment=ajuste
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2)->nullable(); // Price at movement time
            $table->decimal('unit_cost', 10, 2)->nullable(); // Cost at movement time
            $table->string('reference_type', 50)->nullable(); // sale, purchase, transfer, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related record (sale_order_id, etc)
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null'); // For transfers
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null'); // For transfers
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('movement_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
