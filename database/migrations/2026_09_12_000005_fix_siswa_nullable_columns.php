<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Jadikan kolom-kolom opsional di tabel siswa menjadi nullable
 * agar form tambah siswa tidak gagal karena NOT NULL constraint.
 *
 * Kolom yang diperbaiki:
 *   jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, tahun_ajaran
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('siswa')) return;

        // Isi nilai default dulu untuk data lama yang mungkin kosong/null
        DB::table('siswa')->whereNull('jenis_kelamin')->update(['jenis_kelamin' => 'L']);
        DB::table('siswa')->whereNull('tempat_lahir')->update(['tempat_lahir' => '-']);
        DB::table('siswa')->whereNull('tanggal_lahir')->update(['tanggal_lahir' => '2000-01-01']);
        DB::table('siswa')->whereNull('alamat')->update(['alamat' => '-']);
        DB::table('siswa')->whereNull('tahun_ajaran')->update(['tahun_ajaran' => date('Y') . '/' . (date('Y') + 1)]);

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('jenis_kelamin', 10)->nullable()->default(null)->change();
            $table->string('tempat_lahir', 100)->nullable()->default(null)->change();
            $table->date('tanggal_lahir')->nullable()->default(null)->change();
            $table->text('alamat')->nullable()->change();
            $table->string('tahun_ajaran', 20)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('siswa')) return;

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('jenis_kelamin', 10)->nullable(false)->change();
            $table->string('tempat_lahir', 100)->nullable(false)->change();
            $table->date('tanggal_lahir')->nullable(false)->change();
            $table->text('alamat')->nullable(false)->change();
            $table->string('tahun_ajaran', 20)->nullable(false)->change();
        });
    }
};
