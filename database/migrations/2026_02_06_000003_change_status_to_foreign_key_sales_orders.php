<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
      public function up(): void
      {
            Schema::table('sales_orders', function (Blueprint $table) {
                  // Agregamos la nueva columna como foreign key
                  $table->foreignId('order_status_id')
                        ->after('warehouse_id')
                        ->constrained('order_statuses')
                        ->onDelete('restrict');
            });
      }

      public function down(): void
      {
            Schema::table('sales_orders', function (Blueprint $table) {
                  $table->dropForeignIdFor('orderStatus');
                  $table->dropColumn('order_status_id');
            });           
      }
};
