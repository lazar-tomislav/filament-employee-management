<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->json('phone_numbers')->nullable()->after('email');
        });

        // Migrate existing data
        $employees = DB::table('employees')->whereNotNull('phone_number')->get();
        foreach ($employees as $employee) {
            if (!empty($employee->phone_number)) {
                $phoneNumbers = [
                    [
                        'number' => $employee->phone_number,
                        'type' => 'private', // Defaulting to private
                    ]
                ];
                DB::table('employees')
                    ->where('id', $employee->id)
                    ->update(['phone_numbers' => json_encode($phoneNumbers)]);
            }
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('phone_number');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('email');
        });

        // Reverse migration
        $employees = DB::table('employees')->whereNotNull('phone_numbers')->get();
        foreach ($employees as $employee) {
            $phoneNumbers = json_decode($employee->phone_numbers, true);
            if (!empty($phoneNumbers) && isset($phoneNumbers[0]['number'])) {
                 DB::table('employees')
                    ->where('id', $employee->id)
                    ->update(['phone_number' => $phoneNumbers[0]['number']]);
            }
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('phone_numbers');
        });
    }
};
