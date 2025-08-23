<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Menambahkan kolom setelah kolom 'position'
            $table->string('plantation_group')->nullable()->after('position');
            $table->string('wilayah')->nullable()->after('plantation_group');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['plantation_group', 'wilayah']);
        });
    }
};
