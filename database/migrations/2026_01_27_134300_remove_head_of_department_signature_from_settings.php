<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove head_of_department_signature from settings table
        DB::table('settings')
            ->where('group', 'human_resources')
            ->where('name', 'head_of_department_signature')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore head_of_department_signature to settings table
        DB::table('settings')->insert([
            'group' => 'human_resources',
            'name' => 'head_of_department_signature',
            'locked' => false,
            'payload' => json_encode(null),
        ]);
    }
};
