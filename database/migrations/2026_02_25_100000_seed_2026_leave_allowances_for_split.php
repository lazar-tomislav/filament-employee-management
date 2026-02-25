<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed LeaveAllowance za 2026. godinu za sve aktivne zaposlenike na rast_split tenantu.
     */
    public function up(): void
    {
        if (config('tenants.active_tenant') !== 'rast_split') {
            return;
        }

        $employeeIds = DB::table('employees')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereNotIn('id', function ($query) {
                $query->select('employee_id')
                    ->from('leave_allowances')
                    ->where('year', 2026)
                    ->whereNull('deleted_at');
            })
            ->pluck('id');

        $now = now();

        $records = $employeeIds->map(fn ($id) => [
            'employee_id' => $id,
            'year' => 2026,
            'total_days' => 29,
            'valid_until_date' => '2027-06-30',
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if (! empty($records)) {
            DB::table('leave_allowances')->insert($records);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('tenants.active_tenant') !== 'rast_split') {
            return;
        }

        DB::table('leave_allowances')
            ->where('year', 2026)
            ->delete();
    }
};
