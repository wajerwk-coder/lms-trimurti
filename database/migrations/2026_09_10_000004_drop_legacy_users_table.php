<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Drop tabel users (legacy).
 *
 * Tabel users dibuat di migration 2024_01_01_000001 sebagai tabel awal sebelum
 * arsitektur beralih ke users_central. Tabel ini sudah tidak dipakai karena:
 *   - Auth guard aktif pakai users_central (config/auth.php)
 *   - LoginController hanya pakai UserCentral (fallback ke users sudah dihapus)
 *   - Tidak ada FK dari tabel lain yang menunjuk ke users.id
 *
 * Sebelum drop: semua user di tabel users sudah di-migrasi ke users_central
 * secara on-the-fly di LoginController (fallback auto-migrate).
 *
 * Prosedur rollback: re-create tabel users dengan skema awal dan copy data dari
 * users_central, tapi ini hanya untuk emergency — normalnya tidak dipakai.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Pastikan semua email di users sudah ada di users_central
        // Jika ada yang belum, migrasi dulu sebelum drop
        if (Schema::hasTable('users') && Schema::hasTable('users_central')) {
            $orphans = DB::table('users')
                ->whereNotIn('email', DB::table('users_central')->pluck('email'))
                ->whereNull(DB::raw('(SELECT deleted_at FROM users u2 WHERE u2.id = users.id LIMIT 1)'))
                ->get();

            foreach ($orphans as $oldUser) {
                // Cek sekali lagi dengan withTrashed
                $exists = DB::table('users_central')
                    ->where('email', $oldUser->email)
                    ->exists();

                if (!$exists) {
                    DB::table('users_central')->insert([
                        'name'              => $oldUser->name,
                        'email'             => $oldUser->email,
                        'username'          => $oldUser->username
                                              ?? str_replace(' ', '_', strtolower($oldUser->name ?? 'user')),
                        'password'          => $oldUser->password,
                        'role'              => $oldUser->role ?? 'siswa',
                        'is_active'         => $oldUser->is_active ?? true,
                        'email_verified_at' => now(),
                        'created_at'        => $oldUser->created_at ?? now(),
                        'updated_at'        => now(),
                    ]);
                }
            }
        }

        // Nonaktifkan FK checks agar drop tidak diblokir constraint dari tabel lain
        // (misal: class_students_student_id_foreign → users.id)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::dropIfExists('users');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Re-create tabel users dengan skema awal
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->enum('role', ['admin', 'guru', 'siswa']);
                $table->string('nis_nip')->unique()->nullable();
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                $table->index('role');
                $table->index('nis_nip');
                $table->index('is_active');
            });

            // Copy data dari users_central untuk emergency rollback
            DB::statement("
                INSERT INTO users (id, name, email, password, role, is_active, created_at, updated_at)
                SELECT id, name, email, password, role, is_active, created_at, updated_at
                FROM users_central
                WHERE deleted_at IS NULL
            ");
        }
    }
};
