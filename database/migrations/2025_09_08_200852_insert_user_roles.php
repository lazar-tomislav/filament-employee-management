<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Uprava (Admin)
        // Ured
        // Montaža
        \Spatie\Permission\Models\Role::query()->updateOrCreate(
            ['name' => 'Uprava (Admin)'],
            [
                'guard_name' => 'web',
                'description' => 'Najviši nivo pristupa, obično rezerviran za direktora i upravitelja tvrtke.',
            ]
        );
        \Spatie\Permission\Models\Role::query()->updateOrCreate(
            ['name' => 'Ured (Administrativno osoblje)'],
            [
                'guard_name' => 'web',
                'description' => 'Najčešće uključuje administrativno osoblje koje upravlja svakodnevnim poslovanjem, ali nema pristupa krucijalnim informacijama.',
            ]
        );
        \Spatie\Permission\Models\Role::query()->updateOrCreate(
            ['name' => 'Montaža'],
            [
                'guard_name' => 'web',
                'description' => 'Najniži nivo pristupa, obično rezerviran za radnike koji trebaju minimalan pristup sustavu.',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
