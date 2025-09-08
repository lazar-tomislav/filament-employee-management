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
            ['name' => 'uprava_admin'],
            [
                'guard_name' => 'web',
                'description' => 'Najviši nivo pristupa, obično rezerviran za direktora i upravitelja tvrtke.',
            ]
        );
        \Spatie\Permission\Models\Role::query()->updateOrCreate(
            ['name' => 'super_admin'],
            [
                'guard_name' => 'web',
                'description' => 'Apsolutni pristup svemu, ima samo programer.',
            ]
        );
        \Spatie\Permission\Models\Role::query()->updateOrCreate(
            ['name' => 'ured_administrativno_osoblje'],
            [
                'guard_name' => 'web',
                'description' => 'Najčešće uključuje administrativno osoblje koje upravlja svakodnevnim poslovanjem, ali nema pristupa krucijalnim informacijama.',
            ]
        );
        \Spatie\Permission\Models\Role::query()->updateOrCreate(
            ['name' => 'zaposlenik_employee'],
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
