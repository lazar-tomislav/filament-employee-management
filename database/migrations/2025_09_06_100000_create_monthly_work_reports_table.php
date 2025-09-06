<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('monthly_work_reports', function (Blueprint $table) {
            $table->id();
            $table->date('for_month');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('denied_at')->nullable();
            $table->text('deny_reason')->nullable();
            $table->decimal('total_available_hours', 8, 2);
            $table->decimal('work_hours', 8, 2);
            $table->decimal('overtime_hours', 8, 2);
            $table->decimal('vacation_hours', 8, 2);
            $table->decimal('sick_leave_hours', 8, 2);
            $table->decimal('other_hours', 8, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_work_reports');
    }
};
