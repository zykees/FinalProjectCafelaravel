<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->string('booking_code')->unique();
            $table->integer('number_of_participants');
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);
            $table->date('activity_date');
            $table->date('activity_end_date')->nullable();
            $table->time('activity_time');
            $table->text('note')->nullable();
            $table->string('payment_slip')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'rejected'])->default('pending');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('admin_comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_bookings');
    }
};