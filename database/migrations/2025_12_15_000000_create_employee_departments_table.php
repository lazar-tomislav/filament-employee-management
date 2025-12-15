<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('employee_departments')) {
            Schema::create('employee_departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });

            // Insert default departments
            $departments = [
                'Ured Uprave',
                'Komunikacije',
                'Poduzetnički centar RaST',
                'Tehnološki park Split',
                'Smart City',
            ];

            foreach ($departments as $department) {
                DB::table('employee_departments')->insert([
                    'name' => $department,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'employee_department_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreignId('employee_department_id')
                    ->nullable()
                    ->constrained('employee_departments')
                    ->nullOnDelete();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'employee_department_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['employee_department_id']);
                $table->dropColumn('employee_department_id');
            });
        }

        Schema::dropIfExists('employee_departments');
    }
};
