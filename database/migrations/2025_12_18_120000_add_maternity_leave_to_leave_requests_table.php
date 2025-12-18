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
        // Using a raw SQL statement to modify the ENUM type is necessary here.
        // doctrine/dbal is not a default dependency and is required for Schema::table()->change().
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('godisnji', 'bolovanje', 'placeni_slobodan_dan', 'porodiljni')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the ENUM options back to the original state.
        // Note: This will fail if there are any records with the 'porodiljni' type.
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('godisnji', 'bolovanje', 'placeni_slobodan_dan')");
    }
};
