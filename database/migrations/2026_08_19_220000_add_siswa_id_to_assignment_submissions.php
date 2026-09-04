<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tambah kolom siswa_id ke assignment_submissions.
 * Kolom ini adalah FK ke users_central.id (sistem baru).
 * Kolom student_id lama tetap ada untuk backward compat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('assignment_submissions', 'siswa_id')) {
                $table->unsignedBigInteger('siswa_id')->nullable()->after('student_id');
                $table->index('siswa_id', 'as_siswa_id_index');
            }
        });

        // Sinkronisasi: isi siswa_id dari student_id yang sudah ada
        DB::statement("
            UPDATE assignment_submissions
            SET siswa_id = student_id
            WHERE siswa_id IS NULL AND student_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('assignment_submissions', 'siswa_id')) {
                $table->dropIndex('as_siswa_id_index');
                $table->dropColumn('siswa_id');
            }
        });
    }
};
