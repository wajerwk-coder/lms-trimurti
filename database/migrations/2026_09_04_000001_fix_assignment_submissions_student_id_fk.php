<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Drop FK constraint pada student_id yang merujuk ke tabel `users` (lama).
 * Tabel users_central adalah tabel user aktif, bukan `users`.
 * student_id akan tetap ada sebagai kolom biasa tanpa FK constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // Drop FK lama yang merujuk ke tabel `users`
            try {
                $table->dropForeign('assignment_submissions_student_id_foreign');
            } catch (\Exception $e) {
                // FK mungkin sudah tidak ada
            }

            // Ubah student_id menjadi nullable tanpa FK constraint
            // (data tetap ada, hanya FK-nya yang dihapus)
            $table->unsignedBigInteger('student_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Tidak perlu rollback karena tabel `users` sudah tidak relevan
    }
};
