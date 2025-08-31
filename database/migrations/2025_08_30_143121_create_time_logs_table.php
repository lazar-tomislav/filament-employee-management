<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('time_logs');

        Schema::create('time_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->date('date');
            $table->decimal('hours', 8, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['planned', 'confirmed'])->default('confirmed');
            $table->enum('log_type', ['radni_sati', 'bolovanje', 'godisnji', 'placeni_slobodan_dan']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_logs');
    }
};
