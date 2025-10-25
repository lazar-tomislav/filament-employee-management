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
        Schema::create('activity_mentions', function (Blueprint $table) {
            $table->primary(['activity_id', 'mentioned_employee_id']); // Kompozitni primarni ključ
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->unsignedBigInteger('mentioned_employee_id');
            $table->foreign('mentioned_employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_mentions');
    }
};
