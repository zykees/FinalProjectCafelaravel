<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('category_name')->nullable(); // เพิ่มตรงนี้
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->decimal('discount_percent', 5, 2)->default(0);
        $table->integer('stock')->default(0);
        $table->integer('minimum_stock')->default(5);
        $table->string('image')->nullable();
        $table->enum('status', ['available', 'unavailable'])->default('available');
        $table->boolean('featured')->default(false);
        $table->timestamps();
        $table->softDeletes();
        });
    }

    public function down()
    {
        
        Schema::dropIfExists('products');
    }
};