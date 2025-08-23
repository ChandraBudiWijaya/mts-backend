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
        Schema::create('geofences', function (Blueprint $table) {
        $table->id();
        $table->integer('dwh_id')->unique()->comment('ID dari MST_LOKASI di DWH');
        $table->string('name');
        $table->string('pg_group')->nullable();
        $table->string('region');
        $table->string('location_code');
        $table->string('area_size')->nullable();
        $table->json('coordinates');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
};
