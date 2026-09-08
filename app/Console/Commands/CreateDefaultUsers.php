<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateDefaultUsers extends Command
{
    protected $signature   = 'lms:create-users';
    protected $description = 'Buat atau reset akun default admin, guru, siswa di users_central';

    public function handle(): int
    {
        if (!DB::getSchemaBuilder()->hasTable('users_central')) {
            $this->error('Tabel users_central tidak ada.');
            return 1;
        }

        $users = [
            [
                'name'              => 'Administrator',
                'email'             => 'admin@lms-trimurti.sch.id',
                'username'          => 'admin',
                'password'          => Hash::make('Admin@2025'),
                'role'              => 'admin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Guru Demo',
                'email'             => 'guru@lms-trimurti.sch.id',
                'username'          => 'guru_demo',
                'password'          => Hash::make('Guru@2025'),
                'role'              => 'guru',
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Siswa Demo',
                'email'             => 'siswa@lms-trimurti.sch.id',
                'username'          => 'siswa_demo',
                'password'          => Hash::make('Siswa@2025'),
                'role'              => 'siswa',
                'is_active'         => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $u) {
            $exists = DB::table('users_central')->where('email', $u['email'])->first();
            if ($exists) {
                DB::table('users_central')->where('email', $u['email'])->update([
                    'password'  => $u['password'],
                    'is_active' => true,
                    'updated_at'=> now(),
                ]);
                $this->info("✅ Updated: {$u['email']}");
            } else {
                DB::table('users_central')->insert(array_merge($u, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $this->info("✅ Created: {$u['email']}");
            }
        }

        $this->newLine();
        $this->info('=== KREDENSIAL LOGIN ===');
        $this->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@lms-trimurti.sch.id', 'Admin@2025'],
                ['Guru',  'guru@lms-trimurti.sch.id',  'Guru@2025'],
                ['Siswa', 'siswa@lms-trimurti.sch.id', 'Siswa@2025'],
            ]
        );

        return 0;
    }
}
