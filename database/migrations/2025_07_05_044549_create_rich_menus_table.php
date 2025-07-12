<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRichMenusTable extends Migration
{
    public function up()
    {
        Schema::create('rich_menus', function (Blueprint $table) {
            $table->id();
            $table->string('rich_menu_id')->unique(); // richMenuId จาก LINE
            $table->string('name');
            $table->string('chat_bar_text');
            $table->json('button_uris'); // เก็บลิงก์ปุ่มเป็น JSON
            $table->string('image_url')->nullable(); // URL รูปจาก Cloudinary
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rich_menus');
    }
}