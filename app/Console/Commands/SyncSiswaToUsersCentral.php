<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SyncSiswaToUsersCentral extends Command
{
    protected $signature   = 'lms:sync-siswa';
    protected $description = 'Sinkronkan data tabel siswa ke users_central agar muncul di manajemen siswa';

    public function handle(): int
    {
        if (!DB::getSchemaBuilder()->hasTable('siswa') || !DB::getSchemaBuilder()->hasTable('users_central')) {
            $this->error('Tabel siswa atau users_central tidak ada.');
            return 1;
        }

        // Ambil semua siswa yang user_id-nya tidak ada di users_central
        $orphans = DB::table('siswa as s')
            ->leftJoin('users_central as u', 'u.id', '=', 's.user_id')
            ->whereNull('u.id')
            ->whereNull('s.deleted_at')
            ->select('s.*')
            ->get();

        $this->info("Ditemukan {$orphans->count()} siswa tanpa akun users_central.");

        $created = 0;
        $skipped = 0;

        foreach ($orphans as $siswa) {
            // Cek apakah ada email di tabel users lama
            $oldUser = DB::table('users')
                ->where('id', $siswa->user_id)
                ->first();

            if ($oldUser) {
                // Cek apakah email sudah ada di users_central
                $exists = DB::table('users_central')
                    ->where('email', $oldUser->email)
                    ->first();

                if ($exists) {
                    // Update user_id di siswa agar mengarah ke users_central yang benar
                    DB::table('siswa')->where('id', $siswa->id)->update(['user_id' => $exists->id]);
                    $this->line("Updated siswa #{$siswa->id} user_id ke {$exists->id} ({$exists->email})");
                    $skipped++;
                    continue;
                }

                // Buat akun baru di users_central
                $newId = DB::table('users_central')->insertGetId([
                    'name'               => $oldUser->name,
                    'email'              => $oldUser->email,
                    'username'           => $oldUser->username ?? 'siswa_' . $siswa->nis,
                    'password'           => $oldUser->password, // pakai hash yang sama
                    'role'               => 'siswa',
                    'is_active'          => $oldUser->is_active ?? true,
                    'email_verified_at'  => $oldUser->email_verified_at ?? now(),
                    'created_at'         => $oldUser->created_at ?? now(),
                    'updated_at'         => now(),
                ]);

                // Update user_id di siswa
                DB::table('siswa')->where('id', $siswa->id)->update(['user_id' => $newId]);
                $this->info("Created users_central #{$newId} untuk siswa #{$siswa->id} ({$oldUser->email})");
                $created++;
            } else {
                // Tidak ada di tabel users lama — buat akun baru dengan data minimal
                $email    = 'siswa' . $siswa->nis . '@lms-trimurti.sch.id';
                $username = 'siswa_' . $siswa->nis;

                // Skip jika email sudah ada
                if (DB::table('users_central')->where('email', $email)->exists()) {
                    $ucId = DB::table('users_central')->where('email', $email)->value('id');
                    DB::table('siswa')->where('id', $siswa->id)->update(['user_id' => $ucId]);
                    $skipped++;
                    continue;
                }

                $newId = DB::table('users_central')->insertGetId([
                    'name'               => $siswa->nama ?? ('Siswa ' . $siswa->nis),
                    'email'              => $email,
                    'username'           => $username,
                    'password'           => Hash::make('Siswa@2025'),
                    'role'               => 'siswa',
                    'is_active'          => true,
                    'email_verified_at'  => now(),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                DB::table('siswa')->where('id', $siswa->id)->update(['user_id' => $newId]);
                $this->info("Created users_central #{$newId} untuk siswa #{$siswa->id} (NIS: {$siswa->nis})");
                $created++;
            }
        }

        $this->newLine();
        $this->info("Selesai. Created: $created, Updated: $skipped");
        return 0;
    }
}
