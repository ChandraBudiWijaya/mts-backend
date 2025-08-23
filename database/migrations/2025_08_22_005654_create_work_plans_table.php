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
        Schema::create('work_plans', function (Blueprint $table) {
        $table->id();
        $table->date('date');
        $table->string('employee_id');
        $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        $table->foreignId('geofence_id')->constrained('geofences')->onDelete('cascade');
        $table->string('spk_number')->nullable();
        $table->text('activity_description');
        $table->enum('status', ['Draft', 'Submitted', 'Approved', 'Rejected'])->default('Draft');
        $table->foreignId('created_by')->constrained('users');
        $table->foreignId('approved_by')->nullable()->constrained('users');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_plans');
    }
};
