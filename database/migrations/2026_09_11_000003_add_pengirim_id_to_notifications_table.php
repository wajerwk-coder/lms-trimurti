<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tambah kolom pengirim_id ke tabel notifications.
 * Controller NotificationAdminController menggunakan pengirim_id
 * tapi kolom tersebut belum ada di DB (migration lama hanya punya created_by).
 *
 * Setelah kolom dibuat, sinkronkan nilai dari created_by → pengirim_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'pengirim_id')) {
                $table->unsignedBigInteger('pengirim_id')->nullable();
                $table->index('pengirim_id');
            }
        });

        // Sinkronkan dari created_by jika sudah ada data
        if (Schema::hasColumn('notifications', 'created_by') &&
            Schema::hasColumn('notifications', 'pengirim_id')) {
            DB::statement("
                UPDATE notifications
                SET pengirim_id = created_by
                WHERE pengirim_id IS NULL AND created_by IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'pengirim_id')) {
                $table->dropIndex(['pengirim_id']);
                $table->dropColumn('pengirim_id');
            }
        });
    }
};
