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
        Schema::create('location_visits', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreignId('geofence_id')->constrained('geofences')->onDelete('cascade');
            $table->timestamp('entry_time')->comment('Waktu pertama kali masuk area');
            $table->timestamp('exit_time')->comment('Waktu terakhir kali keluar area');
            $table->integer('visit_duration_minutes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_visits');
    }
};
