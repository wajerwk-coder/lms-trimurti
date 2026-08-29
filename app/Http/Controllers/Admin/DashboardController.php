<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserCentral;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\Practical;
use App\Models\Attendance;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin'); // ✅ Middleware untuk semua method
    }

    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Get real statistics with error handling
            $stats = $this->getDashboardStats();
            $recentActivities = $this->getRecentActivities();
            $chartData = $this->getChartData();
            $userDistribution = $this->getUserDistribution();
            $attendanceData = $this->getAttendanceData();
            
            return view('admin.dashboard', compact(
                'stats',
                'recentActivities',
                'chartData',
                'userDistribution',
                'attendanceData'
            ));

        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());

            // Return with safe default values
            $stats = [
                'total_users' => 0,
                'total_guru' => 0,
                'total_siswa' => 0,
                'total_materials' => 0,
                'total_assignments' => 0,
                'total_practicals' => 0,
                'new_users_today' => 0,
            ];

            return view('admin.dashboard', [
                'stats' => $stats,
                'recentUsers' => collect(),
                'recentActivities' => [],
                'chartData' => ['months' => [], 'datasets' => []],
                'userDistribution' => ['labels' => [], 'data' => [], 'colors' => []],
                'attendanceData' => ['total' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'attendance_rate' => 0],
                'error' => 'Terjadi kesalahan saat memuat dashboard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get dashboard statistics
     *
     * @return array
     */
    private function getDashboardStats()
    {
        try {
            return [
                'total_users' => UserCentral::count(),
                'total_guru' => UserCentral::where('role', 'guru')->count(),
                'total_siswa' => UserCentral::where('role', 'siswa')->count(),
                'total_admin' => UserCentral::where('role', 'admin')->count(),
                'total_materials' => Material::count(),
                'total_assignments' => Assignment::count(),
                'total_practicals' => Practical::count(),
                'new_users_today' => UserCentral::whereDate('created_at', today())->count(),
                'active_users' => UserCentral::where('is_active', true)->count(),
                'inactive_users' => UserCentral::where('is_active', false)->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting dashboard stats: ' . $e->getMessage());
            return [
                'total_users' => 0,
                'total_guru' => 0,
                'total_siswa' => 0,
                'total_admin' => 0,
                'total_materials' => 0,
                'total_assignments' => 0,
                'total_practicals' => 0,
                'new_users_today' => 0,
                'active_users' => 0,
                'inactive_users' => 0,
            ];
        }
    }

    /**
     * Get recent registered users
     *
     * @return \Illuminate\Support\Collection
     */
    private function getRecentUsers()
    {
        return UserCentral::latest()
            ->take(8)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar' => $this->getUserAvatar($user),
                    'registered_at' => $user->created_at->diffForHumans(),
                    'status' => (isset($user->status) && $user->status === 'active') ? 'Aktif' : 'Non-Aktif'
                ];
            });
    }

    /**
     * Get user avatar URL
     *
     * @param \App\Models\User $user
     * @return string
     */
    private function getUserAvatar($user)
    {
        // Check if user has photo attribute
        if (isset($user->photo) && $user->photo) {
            return asset('storage/' . $user->photo);
        }

        // Use the photo_url accessor from User model
        if (method_exists($user, 'getPhotoUrlAttribute')) {
            return $user->photo_url;
        }

        // Default avatar based on role
        $avatarMap = [
            'admin' => 'images/avatars/admin.png',
            'guru' => 'images/avatars/teacher.png',
            'siswa' => 'images/avatars/student.png'
        ];

        $avatarPath = $avatarMap[$user->role] ?? 'images/default-avatar.png';
        return asset($avatarPath);
    }

    /**
     * Get recent activities
     *
     * @return array
     */
    private function getRecentActivities()
    {
        try {
            $activities = [];

            // Get recent users
            $recentUsers = UserCentral::latest()->take(3)->get();
            foreach ($recentUsers as $user) {
                $activities[] = [
                    'user' => (object)['name' => 'System'],
                    'description' => 'User baru terdaftar: ' . $user->name,
                    'created_at' => $user->created_at
                ];
            }

            // Get recent materials if Material model exists
            try {
                $recentMaterials = Material::latest()->take(3)->get();
                foreach ($recentMaterials as $material) {
                    $activities[] = [
                        'user' => (object)['name' => 'System'],
                        'description' => 'Materi baru ditambahkan: ' . $material->title,
                        'created_at' => $material->created_at
                    ];
                }
            } catch (\Exception $e) {
                // Skip if Material model has issues
            }

            // Sort by created_at
            usort($activities, function($a, $b) {
                return $b['created_at']->timestamp - $a['created_at']->timestamp;
            });

            return array_slice($activities, 0, 10);
            
        } catch (\Exception $e) {
            Log::error('Error getting recent activities: ' . $e->getMessage());
            return [
                [
                    'user' => (object)['name' => 'System'],
                    'description' => 'Dashboard berhasil dimuat',
                    'created_at' => now()
                ]
            ];
        }
    }

    /**
     * Get chart data for dashboard
     *
     * @return array
     */
    private function getChartData()
    {
        $months = [];
        $userData = [];
        $materialData = [];
        $assignmentData = [];

        // Data for the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthName = $month->translatedFormat('M');
            $months[] = $monthName;

            // Get start and end of month
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            // Count new users for this month
            $userData[] = UserCentral::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            // Count new materials for this month
            $materialData[] = Material::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            // Count new assignments for this month
            $assignmentData[] = Assignment::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        }

        return [
            'months' => $months,
            'datasets' => [
                [
                    'label' => 'Pengguna Baru',
                    'data' => $userData,
                    'backgroundColor' => 'rgba(78, 115, 223, 0.2)',
                    'borderColor' => 'rgba(78, 115, 223, 1)',
                    'borderWidth' => 2,
                    'tension' => 0.3
                ],
                [
                    'label' => 'Materi Baru',
                    'data' => $materialData,
                    'backgroundColor' => 'rgba(28, 200, 138, 0.2)',
                    'borderColor' => 'rgba(28, 200, 138, 1)',
                    'borderWidth' => 2,
                    'tension' => 0.3
                ],
                [
                    'label' => 'Tugas Baru',
                    'data' => $assignmentData,
                    'backgroundColor' => 'rgba(246, 194, 62, 0.2)',
                    'borderColor' => 'rgba(246, 194, 62, 1)',
                    'borderWidth' => 2,
                    'tension' => 0.3
                ]
            ]
        ];
    }

    /**
     * Get user distribution data
     *
     * @return array
     */
    private function getUserDistribution()
    {
        return [
            'labels' => ['Admin', 'Guru', 'Siswa'],
            'data' => [
                UserCentral::where('role', 'admin')->count(),
                UserCentral::where('role', 'guru')->count(),
                UserCentral::where('role', 'siswa')->count()
            ],
            'colors' => [
                'rgba(78, 115, 223, 0.8)',
                'rgba(54, 185, 204, 0.8)',
                'rgba(28, 200, 138, 0.8)'
            ]
        ];
    }

    /**
     * Get attendance data for the current month
     *
     * @return array
     */
    private function getAttendanceData()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get attendance statistics for current month
        $attendanceStats = Attendance::select(
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hadir'),
            DB::raw('SUM(CASE WHEN status = "izin" THEN 1 ELSE 0 END) as izin'),
            DB::raw('SUM(CASE WHEN status = "sakit" THEN 1 ELSE 0 END) as sakit'),
            DB::raw('SUM(CASE WHEN status = "alpha" THEN 1 ELSE 0 END) as alpha')
        )
        ->whereYear('date', $currentYear)
        ->whereMonth('date', $currentMonth)
        ->first();

        // Calculate attendance rate
        $total = $attendanceStats->total ?? 0;
        $hadir = $attendanceStats->hadir ?? 0;
        $attendanceRate = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'hadir' => $hadir,
            'izin' => $attendanceStats->izin ?? 0,
            'sakit' => $attendanceStats->sakit ?? 0,
            'alpha' => $attendanceStats->alpha ?? 0,
            'attendance_rate' => $attendanceRate
        ];
    }

    /**
     * API endpoint for chart data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartDataApi(Request $request)
    {
        try {
            $chartData = $this->getChartData();
            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);
        } catch (\Exception $e) {
            Log::error('Chart Data API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to load chart data'
            ], 500);
        }
    }

    /**
     * Clear dashboard cache
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        try {
            \App\Services\CacheService::clearDashboardCache();

            return redirect()->back()
                ->with('success', 'Cache dashboard berhasil dibersihkan');
        } catch (\Exception $e) {
            Log::error('Clear Cache Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal membersihkan cache dashboard');
        }
    }
}
