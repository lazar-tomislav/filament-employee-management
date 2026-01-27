<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_work_reports', function (Blueprint $table) {
            $table->decimal('holiday_hours', 8, 2)->default(0)->after('other_hours');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_work_reports', function (Blueprint $table) {
            $table->dropColumn('holiday_hours');
        });
    }
};
