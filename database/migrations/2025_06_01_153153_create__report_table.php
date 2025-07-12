<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->foreignId('generated_by')->constrained('admins');
            $table->json('data');
            $table->timestamps();

            $table->index(['report_type', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};