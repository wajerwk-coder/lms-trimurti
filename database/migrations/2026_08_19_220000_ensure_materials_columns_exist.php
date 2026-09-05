<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan semua kolom yang dibutuhkan controller ada di tabel materials.
 * Migration ini safe — semua penambahan pakai hasColumn() check.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // guru_id — FK ke users_central
            if (!Schema::hasColumn('materials', 'guru_id')) {
                $table->unsignedBigInteger('guru_id')->nullable()->after('id');
            }

            // subject_id — FK ke subjects
            if (!Schema::hasColumn('materials', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('guru_id');
            }

            // kelas_id — FK ke classes (nullable = untuk semua kelas)
            if (!Schema::hasColumn('materials', 'kelas_id')) {
                $table->unsignedBigInteger('kelas_id')->nullable()->after('subject_id');
            }

            // views_count — counter tampilan
            if (!Schema::hasColumn('materials', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('video_url');
            }

            // downloads_count — counter unduhan
            if (!Schema::hasColumn('materials', 'downloads_count')) {
                $table->unsignedInteger('downloads_count')->default(0)->after('views_count');
            }

            // softDeletes — untuk soft delete
            if (!Schema::hasColumn('materials', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Tambah index jika belum ada
        try {
            Schema::table('materials', function (Blueprint $table) {
                $table->index('guru_id',    'materials_guru_id_idx');
                $table->index('kelas_id',   'materials_kelas_id_idx');
                $table->index('subject_id', 'materials_subject_id_idx');
            });
        } catch (\Exception $e) {
            // Index mungkin sudah ada — abaikan
        }
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $cols = ['guru_id', 'subject_id', 'kelas_id', 'views_count', 'downloads_count', 'deleted_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('materials', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
