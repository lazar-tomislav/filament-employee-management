<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_chat_id')->index()->after('id');
            $table->dateTime("telegram_denied_at")->nullable()->after('telegram_chat_id');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_denied_at']);
        });
    }
};
