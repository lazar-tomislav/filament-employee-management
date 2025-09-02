<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_allowances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('total_days');
            $table->date('valid_until_date')->index(); // most often 31.06 of the next year
            $table->string('notes')->nullable();

            $table->unique(['employee_id', 'year']);

            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_allowances');
    }
};
