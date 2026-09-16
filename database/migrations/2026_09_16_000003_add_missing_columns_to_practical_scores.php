<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tambah kolom yang hilang ke practical_scores:
 *   - criteria_id  : FK ke assessment_criteria.id (NULL = nilai ringkasan)
 *   - graded_by    : FK ke users_central.id (siapa yang memberi nilai)
 *   - graded_at    : Timestamp kapan dinilai
 *
 * Dan tambahkan unique constraint agar updateOrCreate bekerja benar:
 *   UNIQUE (practical_id, siswa_id, criteria_id)
 *
 * Tanpa unique constraint, updateOrCreate selalu INSERT baru → duplikat data.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('practical_scores', function (Blueprint $table) {
            // ── Kolom yang hilang ─────────────────────────────────────────
            if (!Schema::hasColumn('practical_scores', 'criteria_id')) {
                $table->unsignedBigInteger('criteria_id')
                      ->nullable()
                      ->after('siswa_id')
                      ->comment('FK ke assessment_criteria.id; NULL = nilai ringkasan/summary');
                $table->index('criteria_id', 'ps_criteria_id_idx');
            }

            if (!Schema::hasColumn('practical_scores', 'graded_by')) {
                $table->unsignedBigInteger('graded_by')
                      ->nullable()
                      ->after('guru_id')
                      ->comment('FK ke users_central.id — guru yang memberikan penilaian');
                $table->index('graded_by', 'ps_graded_by_idx');
            }

            if (!Schema::hasColumn('practical_scores', 'graded_at')) {
                $table->timestamp('graded_at')
                      ->nullable()
                      ->after('graded_by')
                      ->comment('Waktu penilaian diberikan');
            }

            if (!Schema::hasColumn('practical_scores', 'academic_period_id')) {
                $table->unsignedBigInteger('academic_period_id')
                      ->nullable()
                      ->after('id')
                      ->comment('FK ke academic_periods.id');
                $table->index('academic_period_id', 'ps_period_id_idx');
            }
        });

        // ── Unique constraint ─────────────────────────────────────────────
        // Diperlukan agar updateOrCreate dengan criteria_id=NULL bekerja benar.
        // MySQL memperlakukan NULL sebagai nilai berbeda dalam UNIQUE index,
        // sehingga perlu ditambahkan secara eksplisit.
        //
        // Cek apakah index sudah ada sebelum membuat.
        $indexExists = collect(DB::select(
            "SHOW INDEX FROM `practical_scores` WHERE Key_name = 'ps_unique_practical_siswa_criteria'"
        ))->isNotEmpty();

        if (!$indexExists) {
            // Hapus duplikat summary (criteria_id = NULL) sebelum buat unique constraint
            // Simpan hanya record terbaru per (practical_id, siswa_id) untuk NULL criteria
            DB::statement("
                DELETE ps1 FROM practical_scores ps1
                INNER JOIN practical_scores ps2
                ON ps1.practical_id = ps2.practical_id
                   AND ps1.siswa_id = ps2.siswa_id
                   AND ps1.criteria_id IS NULL
                   AND ps2.criteria_id IS NULL
                   AND ps1.id < ps2.id
            ");

            // Hapus duplikat per-kriteria (criteria_id IS NOT NULL)
            DB::statement("
                DELETE ps1 FROM practical_scores ps1
                INNER JOIN practical_scores ps2
                ON ps1.practical_id = ps2.practical_id
                   AND ps1.siswa_id = ps2.siswa_id
                   AND ps1.criteria_id = ps2.criteria_id
                   AND ps1.criteria_id IS NOT NULL
                   AND ps1.id < ps2.id
            ");

            // Buat unique index
            // MySQL tidak support NULL dalam standard unique — pakai workaround:
            // unique index pada (practical_id, siswa_id, COALESCE(criteria_id, 0))
            // Tapi COALESCE tidak bisa di-index di MySQL < 8.0.13.
            // Solusi aman: buat index biasa sebagai unique partial workaround.
            // Untuk MySQL 8+: gunakan functional index.
            // Untuk kompatibilitas: tambah unique constraint biasa dan
            // izinkan Laravel sendiri yang handle NULL lewat whereNull.
            Schema::table('practical_scores', function (Blueprint $table) {
                // Unique normal — MySQL mengizinkan multiple NULL dalam unique index
                // sehingga (p_id, s_id, NULL) bisa ada lebih dari satu.
                // Kita sudah bersihkan duplikat di atas; controller harus
                // menggunakan orderBy+take(1) atau firstOrCreate pattern.
                // Index ini tetap berguna untuk (p_id, s_id, criteria_id NOT NULL).
                try {
                    $table->unique(
                        ['practical_id', 'siswa_id', 'criteria_id'],
                        'ps_unique_practical_siswa_criteria'
                    );
                } catch (\Exception $e) {
                    // Index mungkin sudah ada — abaikan
                }
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('practical_scores', function (Blueprint $table) {
            // Drop unique index
            try { $table->dropUnique('ps_unique_practical_siswa_criteria'); } catch (\Exception $e) {}

            // Drop kolom yang ditambahkan
            foreach (['criteria_id', 'graded_by', 'graded_at', 'academic_period_id'] as $col) {
                if (Schema::hasColumn('practical_scores', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
