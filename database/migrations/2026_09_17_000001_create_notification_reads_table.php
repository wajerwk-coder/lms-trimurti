<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tabel pivot: status baca notifikasi per-user.
 *
 * Sebelumnya status baca disimpan di kolom read_at di tabel notifications —
 * ini tidak cocok untuk notifikasi broadcast (tipe_penerima = semua/guru/siswa)
 * karena satu record dipakai bersama → jika user A tandai baca, user B juga
 * jadi "sudah baca", dan setelah login ulang status bisa hilang.
 *
 * Solusi: tabel notification_reads menyimpan (user_id, notification_id)
 * sehingga setiap user punya status baca sendiri-sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_reads')) {
            Schema::create('notification_reads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')
                      ->comment('FK ke users_central.id');
                $table->unsignedBigInteger('notification_id')
                      ->comment('FK ke notifications.id');
                $table->timestamp('read_at')->useCurrent()
                      ->comment('Kapan notifikasi ini dibaca oleh user');
                $table->timestamps();

                // Satu user hanya bisa punya satu record per notifikasi
                $table->unique(['user_id', 'notification_id'], 'nr_unique_user_notif');

                $table->index('user_id',          'nr_user_idx');
                $table->index('notification_id',  'nr_notif_idx');

                // FK ke notifications (cascade delete agar bersih saat notif dihapus)
                $table->foreign('notification_id')
                      ->references('id')->on('notifications')
                      ->onDelete('cascade');
            });
        }

        // Migrasi data lama: notifikasi yang sudah punya read_at di tabel notifications
        // dan tipe_penerima = user spesifik (penerima_id IS NOT NULL) → pindahkan ke pivot
        if (Schema::hasTable('notification_reads') && Schema::hasColumn('notifications', 'read_at')) {
            DB::statement("
                INSERT IGNORE INTO notification_reads (user_id, notification_id, read_at, created_at, updated_at)
                SELECT penerima_id, id, read_at, NOW(), NOW()
                FROM notifications
                WHERE read_at IS NOT NULL
                  AND penerima_id IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};
