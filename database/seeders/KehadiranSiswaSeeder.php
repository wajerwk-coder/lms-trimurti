<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KehadiranSiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active classes
        $kelasList = Kelas::aktif()->get();
        
        if ($kelasList->isEmpty()) {
            $this->command->error('No active classes found. Please run KelasSiswaSeeder first.');
            return;
        }

        // Get all students
        $students = User::where('role', 'siswa')->get();
        
        if ($students->isEmpty()) {
            $this->command->error('No students found. Please run KelasSiswaSeeder first.');
            return;
        }

        // Create attendance data for last 30 days (excluding weekends)
        $attendanceData = [];
        $currentDate = Carbon::now();
        
        foreach ($students as $student) {
            // Generate attendance for last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = $currentDate->copy()->subDays($i);
                
                // Skip weekends (Saturday and Sunday)
                if ($date->isWeekend()) {
                    continue;
                }

                // Check if attendance already exists for this student and date
                if (Attendance::where('siswa_id', $student->id)
                    ->whereDate('tanggal', $date->format('Y-m-d'))
                    ->exists()) {
                    continue; // Skip if already exists
                }

                // Generate realistic attendance pattern
                $status = $this->generateRealisticStatus($date, $student);
                
                $attendanceData[] = [
                    'siswa_id' => $student->id,
                    'tanggal' => $date->format('Y-m-d'),
                    'status' => $status,
                    'keterangan' => $this->generateKeterangan($status),
                    'waktu_masuk' => $status === 'hadir' ? $this->generateWaktuMasuk($date) : null,
                    'waktu_keluar' => $status === 'hadir' ? $this->generateWaktuKeluar($date) : null,
                    'recorded_by' => 1, // Admin user ID
                    'type' => 'regular',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert data in chunks to avoid memory issues
        collect($attendanceData)->chunk(200)->each(function ($chunk) {
            Attendance::insert($chunk->toArray());
        });

        $this->command->info('Data kehadiran siswa berhasil dibuat!');
        $this->command->info('Total siswa: ' . $students->count());
        $this->command->info('Total data kehadiran: ' . count($attendanceData));
        $this->command->info('Rentang waktu: 30 hari kerja terakhir');
    }

    /**
     * Generate realistic attendance status based on patterns
     */
    private function generateRealisticStatus(Carbon $date, User $student): string
    {
        // Base attendance rates (can be adjusted for realism)
        $rates = [
            'hadir' => 85,    // 85% hadir
            'izin' => 8,      // 8% izin
            'sakit' => 5,     // 5% sakit
            'alpha' => 2      // 2% alpha
        ];

        // Monday blues - slightly higher absence rate
        if ($date->dayOfWeek === Carbon::MONDAY) {
            $rates['hadir'] = 80;
            $rates['izin'] = 10;
            $rates['sakit'] = 6;
            $rates['alpha'] = 4;
        }

        // Friday - slightly higher permission rate (for religious activities)
        if ($date->dayOfWeek === Carbon::FRIDAY) {
            $rates['hadir'] = 82;
            $rates['izin'] = 12;
            $rates['sakit'] = 4;
            $rates['alpha'] = 2;
        }

        // Random selection based on rates
        $random = mt_rand(1, 100);
        $currentRate = 0;

        foreach ($rates as $status => $rate) {
            $currentRate += $rate;
            if ($random <= $currentRate) {
                return $status;
            }
        }

        return 'hadir'; // Default
    }

    /**
     * Generate realistic keterangan based on status
     */
    private function generateKeterangan(string $status): ?string
    {
        $keterangan = match($status) {
            'izin' => [
                'Izin orang tua',
                'Izin dokter',
                'Izin pribadi',
                'Izin keluarga',
                'Izin acara sekolah',
                'Izin kegiatan agama',
                'Izin urusan bisnis',
                'Izin ke luar kota'
            ],
            'sakit' => [
                'Sakit demam',
                'Sakit kepala',
                'Sakit perut',
                'Sakit flu',
                'Sakit batuk',
                'Sakit diare',
                'Sakit gigi',
                'Sakit mata',
                'Sakit kulit'
            ],
            'alpha' => [
                'Tanpa keterangan',
                'Terlambat datang',
                'Pulang lebih awal',
                'Tidak hadir tanpa alasan',
                'Bolos sekolah',
                'Tidak masuk tanpa izin'
            ],
            default => null
        };

        return $keterangan ? $keterangan[array_rand($keterangan)] : null;
    }

    /**
     * Generate realistic waktu masuk (7:00 - 7:45)
     */
    private function generateWaktuMasuk(Carbon $date): Carbon
    {
        // Most students arrive between 7:00 and 7:45
        $hour = 7;
        $minute = mt_rand(0, 45);
        
        // Some students are late (after 7:15)
        if (mt_rand(1, 100) <= 20) { // 20% chance of being late
            $minute = mt_rand(15, 45);
        }
        
        return $date->copy()->setTime($hour, $minute, mt_rand(0, 59));
    }

    /**
     * Generate realistic waktu keluar (14:00 - 14:30)
     */
    private function generateWaktuKeluar(Carbon $date): Carbon
    {
        // Most students leave between 14:00 and 14:30
        $hour = 14;
        $minute = mt_rand(0, 30);
        
        // Some students leave early (before 14:00)
        if (mt_rand(1, 100) <= 10) { // 10% chance of leaving early
            $minute = mt_rand(0, 15);
            $hour = 13;
        }
        
        return $date->copy()->setTime($hour, $minute, mt_rand(0, 59));
    }
}
