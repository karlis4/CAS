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
        Schema::create('cameras_expluatation_info', function (Blueprint $table) {
            $table->id();
            $table->string('currentCorp')->nullable();
            $table->string('currentPerson')->nullable();
            $table->string('dateExpluatation')->nullable();
            $table->string('dateGuarantee')->nullable();
            $table->string('inventNumber')->nullable();

            $table->foreignId('camera_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cameras_expluatation_info');
    }
};
