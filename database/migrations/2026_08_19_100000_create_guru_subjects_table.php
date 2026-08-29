<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Buat tabel pivot guru_subjects ────────────────────────────────
        if (!Schema::hasTable('guru_subjects')) {
            Schema::create('guru_subjects', function (Blueprint $table) {
                $table->id();
                // guru_id → gurus.id (profil guru, bukan users_central.id)
                $table->unsignedBigInteger('guru_id');
                $table->foreignId('subject_id')
                      ->constrained('subjects')
                      ->onDelete('cascade');
                $table->timestamps();

                $table->foreign('guru_id')
                      ->references('id')->on('gurus')
                      ->onDelete('cascade');

                $table->unique(['guru_id', 'subject_id']);
                $table->index('guru_id');
                $table->index('subject_id');
            });
        }

        // ── Seed awal: migrasikan data string mata_pelajaran → pivot ──────
        // Ambil semua guru yang punya mata_pelajaran terisi
        $gurus = DB::table('gurus')
            ->whereNotNull('mata_pelajaran')
            ->where('mata_pelajaran', '!=', '')
            ->get(['id', 'mata_pelajaran']);

        foreach ($gurus as $guru) {
            // mata_pelajaran bisa berupa "Keperawatan Dasar" atau
            // "Keperawatan Dasar, Farmakologi" (comma-separated)
            $names = array_map('trim', explode(',', $guru->mata_pelajaran));

            foreach ($names as $name) {
                if (empty($name)) continue;

                // Cari subject berdasarkan nama (case-insensitive LIKE)
                $subject = DB::table('subjects')
                    ->where('is_active', true)
                    ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                    ->first();

                if (!$subject) {
                    // Coba partial match
                    $subject = DB::table('subjects')
                        ->where('is_active', true)
                        ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%'])
                        ->first();
                }

                if ($subject) {
                    // Insert ke pivot jika belum ada
                    DB::table('guru_subjects')->insertOrIgnore([
                        'guru_id'    => $guru->id,
                        'subject_id' => $subject->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_subjects');
    }
};
