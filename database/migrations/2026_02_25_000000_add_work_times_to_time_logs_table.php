<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('time_logs', function (Blueprint $table) {
            $table->time('work_start_time')->nullable()->after('hours');
            $table->time('work_end_time')->nullable()->after('work_start_time');
        });

        // Retroaktivno popuni default vrijednosti za sve postojeće unose
        $activeTenant = config('tenants.active_tenant');
        $defaultStart = config("tenants.tenants.{$activeTenant}.features.time_logs.default_start_time", '07:00');
        $defaultEnd = config("tenants.tenants.{$activeTenant}.features.time_logs.default_end_time", '15:00');

        DB::table('time_logs')
            ->whereNull('work_start_time')
            ->update([
                'work_start_time' => $defaultStart,
                'work_end_time' => $defaultEnd,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_logs', function (Blueprint $table) {
            $table->dropColumn(['work_start_time', 'work_end_time']);
        });
    }
};
