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
        Schema::create('users', function (Blueprint $table) {
            // Primary Key
            $table->id();
            
            // User Information
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
             
            // Profile Information
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();
            $table->date('birth_date')->nullable();
            
            // Social Login
            $table->string('google_id')->nullable();
            $table->string('line_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            
            // Settings & Preferences
            $table->json('preferences')->nullable();
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->string('locale')->default('th');
            
            // Security
            $table->rememberToken();
            $table->string('reset_token')->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['email', 'status']);
            $table->index('google_id');
            $table->index('line_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};