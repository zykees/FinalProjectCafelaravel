<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
         Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('promotion_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_code');
            $table->decimal('total_amount', 10, 2);
            $table->string('shipping_name');
            $table->text('shipping_address');
            $table->string('shipping_phone');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('payment_slip')->nullable();
            $table->datetime('payment_date')->nullable();
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};