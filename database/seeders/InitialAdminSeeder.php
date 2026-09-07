<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder minimal — hanya buat satu akun admin di users_central.
 * Dipanggil saat deploy awal Railway agar bisa login langsung.
 *
 * Kredensial default:
 *   Email    : admin@lms-trimurti.sch.id
 *   Password : Admin@2025
 *   Role     : admin
 */
class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya buat jika tabel ada dan belum ada admin sama sekali
        if (!DB::getSchemaBuilder()->hasTable('users_central')) {
            $this->command->warn('Tabel users_central belum ada, skip seeder.');
            return;
        }

        $exists = DB::table('users_central')
            ->where('email', 'admin@lms-trimurti.sch.id')
            ->exists();

        if ($exists) {
            $this->command->info('Admin sudah ada, skip.');
            return;
        }

        DB::table('users_central')->insert([
            'name'               => 'Administrator',
            'email'              => 'admin@lms-trimurti.sch.id',
            'username'           => 'admin',
            'password'           => Hash::make('Admin@2025'),
            'role'               => 'admin',
            'is_active'          => true,
            'email_verified_at'  => now(),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $this->command->info('✅ Admin default berhasil dibuat.');
        $this->command->info('   Email    : admin@lms-trimurti.sch.id');
        $this->command->info('   Password : Admin@2025');
    }
}
