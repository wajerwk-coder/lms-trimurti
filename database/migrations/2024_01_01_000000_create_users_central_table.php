<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel utama autentikasi untuk admin, guru, dan siswa.
 * Dibuat sebelum semua tabel lain karena menjadi target FK banyak tabel.
 * Timestamp 000000 memastikan ini dijalankan pertama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users_central')) {
            return; // Sudah ada, skip
        }

        Schema::create('users_central', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique()->nullable();
            $table->string('password');
            $table->string('role')->default('siswa'); // admin, guru, siswa
            $table->string('phone', 20)->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_central');
    }
};
