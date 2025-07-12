<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_bookings', function (Blueprint $table) {
            $table->dateTime('payment_date')->nullable()->after('payment_slip');
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('promotion_bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_date', 'payment_amount']);
        });
    }
};