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
        Schema::create('daily_summaries', function (Blueprint $table) {
        $table->id();
        $table->date('date');
        $table->string('employee_id');
        $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        $table->float('total_work_minutes')->default(0);
        $table->float('total_outside_area_minutes')->default(0);
        $table->float('total_distance_km')->default(0);
        $table->timestamp('last_update');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
