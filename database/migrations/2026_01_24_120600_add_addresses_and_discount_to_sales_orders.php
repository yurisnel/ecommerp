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
        Schema::table('sales_orders', function (Blueprint $table) {
            // Add foreign keys to customer addresses
            $table->foreignId('shipping_address_id')->nullable()->after('warehouse_id')->constrained('customer_addresses')->onDelete('set null');
            $table->foreignId('billing_address_id')->nullable()->after('shipping_address_id')->constrained('customer_addresses')->onDelete('set null');
            
            // Add discount tracking
            $table->foreignId('discount_rule_id')->nullable()->after('discount_total')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_address_id']);
            $table->dropForeign(['billing_address_id']);
            $table->dropForeign(['discount_rule_id']);
            $table->dropColumn(['shipping_address_id', 'billing_address_id', 'discount_rule_id']);
        });
    }
};
