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
        Schema::create('mst_params', function (Blueprint $table) {
            $table->id();
            $table->string('param_key')->unique();
            $table->text('param_value');
            $table->text('description')->nullable();
            $table->string('group_name')->nullable();

            // INI PENAMBAHANNYA
            $table->boolean('status')->default(true); // true = Aktif, false = Tidak Aktif

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_params');
    }
};
