<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('siswa')) return;

        Schema::table('siswa', function (Blueprint $table) {
            // Jadikan nullable agar insert tanpa no_telepon tidak error NOT NULL
            $table->string('no_telepon', 20)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('siswa')) return;

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('no_telepon', 20)->nullable(false)->change();
        });
    }
};
