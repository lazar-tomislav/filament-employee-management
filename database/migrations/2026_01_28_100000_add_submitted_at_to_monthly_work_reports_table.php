<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_work_reports', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('for_month');
            $table->foreignId('submitted_by_user_id')->nullable()->after('submitted_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('monthly_work_reports', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_user_id']);
            $table->dropColumn(['submitted_at', 'submitted_by_user_id']);
        });
    }
};
