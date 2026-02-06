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
        Schema::create('payment_method_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_method_id')->constrained()->onDelete('cascade');
            $table->string('key', 100); // Config key (e.g., 'api_key', 'secret_key', 'merchant_id')
            $table->text('value')->nullable(); // Config value (encrypted if sensitive)
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
            
            $table->unique(['payment_method_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_configs');
    }
};
