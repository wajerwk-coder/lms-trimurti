<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:users {--role=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users or filter by role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = $this->option('role');
        
        $this->info('📊 Daftar User di LMS Trimurti Husada');
        $this->newLine();
        
        $query = User::query();
        
        if ($role) {
            $query->where('role', $role);
            $this->line("🔍 Filter: Role = {$role}");
        }
        
        $users = $query->orderBy('created_at', 'desc')->get();
        
        if ($users->isEmpty()) {
            $this->warn('😕 Tidak ada user ditemukan');
            return;
        }
        
        $headers = ['ID', 'Nama', 'Email', 'Role', 'Status', 'Dibuat'];
        $data = [];
        
        foreach ($users as $user) {
            $roleIcon = match($user->role) {
                'admin' => '👨‍💼',
                'guru' => '👨‍🏫',
                'siswa' => '👨‍🎓',
                default => '👤'
            };
            
            $statusIcon = $user->status === 'active' ? '✅' : '❌';
            
            $data[] = [
                $user->id,
                $user->name,
                $user->email,
                $roleIcon . ' ' . $user->role,
                $statusIcon . ' ' . $user->status,
                $user->created_at->format('d/m/Y H:i')
            ];
        }
        
        $this->table($headers, $data);
        
        $this->newLine();
        $this->line('📋 Total: ' . $users->count() . ' user(s)');
        
        // Show statistics
        $stats = [
            'Admin' => User::where('role', 'admin')->count(),
            'Guru' => User::where('role', 'guru')->count(),
            'Siswa' => User::where('role', 'siswa')->count(),
            'Active' => User::where('is_active', true)->count(),
            'Inactive' => User::where('is_active', false)->count(),
        ];
        
        $this->line('');
        $this->line('📈 Statistik:');
        foreach ($stats as $label => $count) {
            $this->line("  {$label}: {$count}");
        }
        
        $this->newLine();
        $this->line('🚀 Commands:');
        $this->line('  Buat user baru: php artisan create:user');
        $this->line('  Filter admin: php artisan list:users --role=admin');
        $this->line('  Filter guru: php artisan list:users --role=guru');
        $this->line('  Filter siswa: php artisan list:users --role=siswa');
    }
}
