<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hapus path foto lokal dari DB di Railway.
 * Foto lokal tidak bisa diakses karena Railway pakai ephemeral storage.
 * Setelah migration ini, foto akan fallback ke ui-avatars hingga user upload ulang via Cloudinary.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Set photo = NULL untuk semua user yang photonya bukan URL http/https
        // (artinya path lokal seperti profiles/admin/xxx.jpg)
        DB::table('users_central')
            ->whereNotNull('photo')
            ->where('photo', 'not like', 'http%')
            ->update(['photo' => null]);
    }

    public function down(): void
    {
        // Tidak bisa di-rollback
    }
};
