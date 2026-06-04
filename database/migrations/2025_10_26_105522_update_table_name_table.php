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
        Schema::table('cameras', function (Blueprint $table) {
            // Удаляем столбец
            $table->dropColumn('geolocation');

            // Добавляем первый новый столбец
            $table->string('latitude');

            // Добавляем второй новый столбец
            $table->string('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
