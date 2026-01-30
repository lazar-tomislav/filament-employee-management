<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('approved_by_head_of_department_id')
                ->nullable()
                ->after('rejection_reason')
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('approved_by_director_id')
                ->nullable()
                ->after('approved_by_head_of_department_id')
                ->constrained('employees')
                ->nullOnDelete();

            $table->timestamp('approved_by_head_of_department_at')
                ->nullable()
                ->after('approved_by_director_id');

            $table->timestamp('approved_by_director_at')
                ->nullable()
                ->after('approved_by_head_of_department_at');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_head_of_department_id']);
            $table->dropForeign(['approved_by_director_id']);
            $table->dropColumn([
                'approved_by_head_of_department_id',
                'approved_by_director_id',
                'approved_by_head_of_department_at',
                'approved_by_director_at',
            ]);
        });
    }
};
