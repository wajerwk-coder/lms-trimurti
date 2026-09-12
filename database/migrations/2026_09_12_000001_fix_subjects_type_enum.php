<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Perbaiki definisi ENUM kolom 'type' di tabel subjects.
     * Gabungkan semua nilai lama + nilai baru 'campuran' agar tidak ada data
     * yang ter-truncate dan controller bisa menyimpan nilai 'campuran'.
     *
     * Nilai lama di production: teori, praktik, praktikum, teori_praktik,
     *                            laboratorium, klinik, magang
     * Nilai baru yang dibutuhkan: teori, praktikum, campuran
     */
    public function up(): void
    {
        // Langkah 1: expand ENUM dulu mencakup SEMUA nilai lama + baru
        // agar UPDATE berikutnya tidak ditolak strict mode
        DB::statement("ALTER TABLE `subjects`
            MODIFY COLUMN `type`
            ENUM('teori','praktik','praktikum','teori_praktik','laboratorium','klinik','magang','campuran')
            NOT NULL DEFAULT 'teori'");

        // Langkah 2: normalisasi nilai lama → nilai standar baru
        DB::statement("UPDATE `subjects` SET `type` = 'campuran'
            WHERE `type` IN ('teori_praktik', 'laboratorium', 'klinik', 'magang')");

        DB::statement("UPDATE `subjects` SET `type` = 'praktikum'
            WHERE `type` = 'praktik'");

        // Langkah 3: sekarang aman untuk menyempitkan ENUM ke nilai standar saja
        DB::statement("ALTER TABLE `subjects`
            MODIFY COLUMN `type`
            ENUM('teori', 'praktikum', 'campuran')
            NOT NULL DEFAULT 'teori'");
    }

    public function down(): void
    {
        // Kembalikan ke ENUM lama yang lebih luas agar tidak ada data hilang
        DB::statement("ALTER TABLE `subjects`
            MODIFY COLUMN `type`
            ENUM('teori','praktik','praktikum','teori_praktik','laboratorium','klinik','magang')
            NOT NULL DEFAULT 'teori'");
    }
};
