<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('activity_details');
            $table->integer('max_participants');
            $table->integer('current_participants')->default(0);
            $table->decimal('price_per_person', 10, 2);
            $table->decimal('discount', 5, 2)->default(0);
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->string('location');
            $table->text('included_items')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotions');
    }
};