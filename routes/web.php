<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\FileController;

// ✅ USE STATEMENTS UNTUK CONTROLLERS
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ModernUserController;
use App\Http\Controllers\Admin\MaterialController as AdminMaterialController;
use App\Http\Controllers\Admin\AssignmentController as AdminAssignmentController;
use App\Http\Controllers\Admin\PracticalController as AdminPracticalController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
// use App\Http\Controllers\Admin\SettingController as AdminSettingController;   // belum dibuat
// use App\Http\Controllers\Admin\ReportController as AdminReportController;     // belum dibuat
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ExamScheduleController as AdminExamScheduleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\KriteriaPenilaianController;

use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\MaterialController as GuruMaterialController;
use App\Http\Controllers\Guru\AssignmentController as GuruAssignmentController;
use App\Http\Controllers\Guru\AttendanceController as GuruAttendanceController;
use App\Http\Controllers\Guru\AttendanceControllerNew;
// use App\Http\Controllers\Guru\ScoringController as GuruScoringController;    // belum dibuat
use App\Http\Controllers\Guru\ReportController as GuruReportController;
use App\Http\Controllers\Guru\ProfileController as GuruProfileController;
// use App\Http\Controllers\Guru\SubmissionController as GuruSubmissionController; // belum dibuat (pakai SubmissionsController)
use App\Http\Controllers\Guru\SubmissionsController;
use App\Http\Controllers\Guru\PenilaianController as GuruPenilaianController;
use App\Http\Controllers\Guru\PracticalController as GuruPracticalController;

use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\MaterialController as SiswaMaterialController;
use App\Http\Controllers\Siswa\AssignmentController as SiswaAssignmentController;
use App\Http\Controllers\Siswa\PracticalController as SiswaPracticalController;
use App\Http\Controllers\Siswa\ScoreController as SiswaScoreController;
use App\Http\Controllers\Siswa\AttendanceController as SiswaAttendanceController;
use App\Http\Controllers\Siswa\ProfileController as SiswaProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'welcome'])->name('welcome');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('contact.send');
Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard')->middleware('auth');

// Route untuk guest (belum login)
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    // Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    // Route::post('register', [RegisterController::class, 'register']);

    // Password Reset Routes
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout harus tersedia untuk semua yang terautentikasi
Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('web')
    ->name('logout');

// ✅ ADMIN ROUTES - middleware digabung dalam array
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Sistem User Terpisah (NEW) - MUST BE BEFORE RESOURCE ROUTE
    Route::get('/users/separated', [ModernUserController::class, 'index'])->name('users.separated');
    Route::get('/users/guru', [ModernUserController::class, 'guruIndex'])->name('users.guru');
    Route::get('/users/siswa', [ModernUserController::class, 'siswaIndex'])->name('users.siswa');
    Route::get('/users/create/admin', [ModernUserController::class, 'createAdmin'])->name('users.create.admin');
    Route::get('/users/create/guru', [ModernUserController::class, 'createGuru'])->name('users.create.guru');
    Route::get('/users/create/siswa', [ModernUserController::class, 'createSiswa'])->name('users.create.siswa');
    Route::post('/users/store/admin', [ModernUserController::class, 'storeAdmin'])->name('users.store.admin');
    Route::post('/users/store/guru', [ModernUserController::class, 'storeGuru'])->name('users.store.guru');
    Route::post('/users/store/siswa', [ModernUserController::class, 'storeSiswa'])->name('users.store.siswa');
    
    // Kelola Data User — CUSTOM ROUTES HARUS SEBELUM RESOURCE
    Route::post('users/bulk-action', [AdminUserController::class, 'bulkAction'])->name('users.bulk-action');
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
    
    // Kelola Kelas
    Route::resource('kelas', KelasController::class);
    
    // Kelola Jurusan
    Route::resource('jurusan', JurusanController::class);
    
    // Kelola Mata Pelajaran — CUSTOM ROUTES HARUS SEBELUM RESOURCE
    Route::post('mata-pelajaran/seed-default', [MataPelajaranController::class, 'seedDefault'])->name('mata-pelajaran.seed-default');
    Route::resource('mata-pelajaran', MataPelajaranController::class);
    Route::post('mata-pelajaran/{mata_pelajaran}/toggle-status', [MataPelajaranController::class, 'toggleStatus'])->name('mata-pelajaran.toggle-status');
    
    // Kelola Kriteria Penilaian Praktik — CUSTOM ROUTES SEBELUM RESOURCE
    Route::get('kriteria-penilaian/create-combined',  [KriteriaPenilaianController::class, 'createCombined'])->name('kriteria-penilaian.create-combined');
    Route::post('kriteria-penilaian/store-combined',  [KriteriaPenilaianController::class, 'storeCombined'])->name('kriteria-penilaian.store-combined');
    Route::post('kriteria-penilaian/seed-default',    [KriteriaPenilaianController::class, 'seedDefault'])->name('kriteria-penilaian.seed-default');
    Route::get('kriteria-penilaian/get-by-mata',      [KriteriaPenilaianController::class, 'getKriteria'])->name('kriteria-penilaian.get-by-mata');
    Route::post('kriteria-penilaian/{kriteriaPenilaian}/toggle-status', [KriteriaPenilaianController::class, 'toggleStatus'])->name('kriteria-penilaian.toggle-status');
    Route::post('kriteria-penilaian/{kriteriaPenilaian}/duplicate',     [KriteriaPenilaianController::class, 'duplicate'])->name('kriteria-penilaian.duplicate');
    Route::get('kriteria-penilaian/export-template',  [KriteriaPenilaianController::class, 'exportTemplate'])->name('kriteria-penilaian.export-template');
    Route::resource('kriteria-penilaian', KriteriaPenilaianController::class);

    // Kelola Materi — BULK ROUTES SEBELUM RESOURCE
    Route::delete('materials/bulk-delete', [AdminMaterialController::class, 'bulkDelete'])->name('materials.bulk-delete');
    Route::post('materials/bulk-delete',   [AdminMaterialController::class, 'bulkDelete'])->name('materials.bulk-delete.post');
    Route::resource('materials', AdminMaterialController::class);
    Route::post('materials/{material}/toggle-publish', [AdminMaterialController::class, 'togglePublish'])->name('materials.toggle-publish');
    Route::post('materials/{material}/publish',        [AdminMaterialController::class, 'togglePublish'])->name('materials.publish');

    // Kelola Tugas — BULK ROUTES SEBELUM RESOURCE
    Route::delete('assignments/bulk-delete', [AdminAssignmentController::class, 'bulkDelete'])->name('assignments.bulk-delete');
    Route::post('assignments/bulk-delete',   [AdminAssignmentController::class, 'bulkDelete'])->name('assignments.bulk-delete.post');
    Route::resource('assignments', AdminAssignmentController::class);
    Route::post('assignments/{assignment}/toggle-publish', [AdminAssignmentController::class, 'togglePublish'])->name('assignments.toggle-publish');
    Route::post('assignments/{assignment}/publish',        [AdminAssignmentController::class, 'togglePublish'])->name('assignments.publish');

    // Kelola Praktikum — BULK ROUTES SEBELUM RESOURCE
    Route::delete('practicals/bulk-delete', [AdminPracticalController::class, 'bulkDelete'])->name('practicals.bulk-delete');
    Route::post('practicals/bulk-delete',   [AdminPracticalController::class, 'bulkDelete'])->name('practicals.bulk-delete.post');
    Route::resource('practicals', AdminPracticalController::class);
    Route::post('practicals/{practical}/toggle-publish', [AdminPracticalController::class, 'togglePublish'])->name('practicals.toggle-publish');
    Route::post('practicals/{practical}/publish',        [AdminPracticalController::class, 'togglePublish'])->name('practicals.publish');

    // Kelola Absensi
    Route::resource('attendance', AdminAttendanceController::class);
    Route::post('attendance/bulk-update', [AdminAttendanceController::class, 'bulkUpdate'])->name('attendance.bulk-update');
    
    // Kelola Jadwal Ujian
    Route::resource('exam-schedules', AdminExamScheduleController::class);
    Route::post('exam-schedules/{examSchedule}/publish', [AdminExamScheduleController::class, 'publish'])->name('exam-schedules.publish');
    
    // Settings (sementara dikomen sampai controller dibuat)
    // Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    // Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    // Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
    // Route::get('backup', [AdminBackupController::class, 'index'])->name('backup.index');
    // Route::post('backup/create', [AdminBackupController::class, 'create'])->name('backup.create');
    
    // Profile
    Route::get('profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/update-photo-url', [AdminProfileController::class, 'updatePhotoUrl'])->name('profile.update-photo-url');
});

// ✅ GURU ROUTES - middleware digabung dalam array
Route::prefix('guru')->name('guru.')->middleware(['auth', 'guru'])->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
    
    // Materials Management — BULK ROUTES MUST BE BEFORE RESOURCE
    Route::post('materials/bulk-delete',    [GuruMaterialController::class, 'bulkDelete'])->name('materials.bulk-delete');
    Route::post('materials/bulk-publish',   [GuruMaterialController::class, 'bulkPublish'])->name('materials.bulk-publish');
    Route::post('materials/bulk-unpublish', [GuruMaterialController::class, 'bulkUnpublish'])->name('materials.bulk-unpublish');
    Route::resource('materials', GuruMaterialController::class);
    Route::get('materials/{material}/download',       [GuruMaterialController::class, 'download'])->name('materials.download');
    Route::post('materials/{material}/toggle-publish',[GuruMaterialController::class, 'togglePublish'])->name('materials.toggle-publish');
    
    // Assignments Management
    Route::resource('assignments', GuruAssignmentController::class);
    Route::get('assignments/{assignment}/submissions', [GuruAssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::post('assignments/{assignment}/submissions/{submission}/grade', [GuruAssignmentController::class, 'grade'])->name('assignments.grade');
    
    // Praktikum Management
    Route::post('praktikum/{praktikum}/toggle-publish', [GuruPracticalController::class, 'togglePublish'])->name('praktikum.toggle-publish');
    Route::resource('praktikum', GuruPracticalController::class);
    // Praktikums (English alias) - to support legacy route('guru.practicals.*') usage in views
    Route::resource('practicals', GuruPracticalController::class)->names([
        'index' => 'practicals.index',
        'create' => 'practicals.create',
        'store' => 'practicals.store',
        'show' => 'practicals.show',
        'edit' => 'practicals.edit',
        'update' => 'practicals.update',
        'destroy' => 'practicals.destroy'
    ]);
    
    // Attendance Management
    Route::resource('attendance', GuruAttendanceController::class);
    Route::post('attendance/bulk', [GuruAttendanceController::class, 'bulkStore'])->name('attendance.bulk');
    Route::get('attendance/report', function () {
        abort(404);
    })->name('attendance.report');
    
    // Absensi Management — CUSTOM ROUTES SEBELUM RESOURCE
    Route::get('absensi/bulk-create', [GuruAttendanceController::class, 'bulkCreate'])->name('absensi.bulk-create');
    Route::get('absensi/siswa-by-kelas', [GuruAttendanceController::class, 'siswaByKelas'])->name('absensi.siswa-by-kelas');
    Route::post('absensi/bulk', [GuruAttendanceController::class, 'bulkStore'])->name('absensi.bulk');
    Route::get('absensi/praktik', [GuruAttendanceController::class, 'praktikAttendance'])->name('absensi.praktik');
    Route::get('absensi/report', function () { abort(404); })->name('absensi.report');

    Route::resource('absensi', GuruAttendanceController::class)->names([
        'index'   => 'absensi.index',
        'create'  => 'absensi.create',
        'store'   => 'absensi.store',
        'show'    => 'absensi.show',
        'edit'    => 'absensi.edit',
        'update'  => 'absensi.update',
        'destroy' => 'absensi.destroy',
    ]);
    
    // Praktik attendance routes removed
    
    // New Attendance Management (Enhanced)
    Route::prefix('absensi-new')->name('absensi-new.')->group(function () {
        Route::get('/', [AttendanceControllerNew::class, 'index'])->name('index');
        Route::get('/create', [AttendanceControllerNew::class, 'create'])->name('create');
        Route::post('/', [AttendanceControllerNew::class, 'store'])->name('store');
        Route::get('/{attendance}/edit', [AttendanceControllerNew::class, 'edit'])->name('edit');
        Route::put('/{attendance}', [AttendanceControllerNew::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AttendanceControllerNew::class, 'destroy'])->name('destroy');
    });
    
    // Submissions Management
    Route::get('submissions', [SubmissionsController::class, 'index'])->name('submissions.index');
    Route::get('submissions/{submission}', [SubmissionsController::class, 'show'])->name('submissions.show');
    Route::post('submissions/{submission}/grade', [SubmissionsController::class, 'grade'])->name('submissions.grade');
    
    // Penilaian (Grading) Management
    // ── SISTEM BARU berbasis kriteria admin ── HARUS SEBELUM resource/wildcard
    Route::get('penilaian/nilai-kriteria', [GuruPenilaianController::class, 'nilaiKriteria'])->name('penilaian.nilai-kriteria');
    Route::post('penilaian/nilai-kriteria', [GuruPenilaianController::class, 'storeNilaiKriteria'])->name('penilaian.nilai-kriteria.store');
    Route::get('penilaian/kriteria-by-practical', [GuruPenilaianController::class, 'getKriteriaByPractical'])->name('penilaian.kriteria-by-practical');

    // ── Halaman khusus Penilaian Praktik ────────────────────────────────────
    Route::get('penilaian-praktik', [GuruPenilaianController::class, 'penilaianPraktik'])->name('penilaian-praktik.index');

    Route::get('penilaian', [GuruPenilaianController::class, 'index'])->name('penilaian.index');
    Route::get('penilaian/create', [GuruPenilaianController::class, 'create'])->name('penilaian.create');
    Route::post('penilaian', [GuruPenilaianController::class, 'store'])->name('penilaian.store');
    Route::get('penilaian/{submission}/edit', [GuruPenilaianController::class, 'edit'])->name('penilaian.edit');
    Route::put('penilaian/{submission}', [GuruPenilaianController::class, 'update'])->name('penilaian.update');
    Route::delete('penilaian/{submission}', [GuruPenilaianController::class, 'destroy'])->name('penilaian.destroy');
    
    // Auto Assessment for Practical
    Route::get('penilaian/auto', [GuruPenilaianController::class, 'autoAssessment'])->name('penilaian.auto');
    Route::post('penilaian/auto/save', [GuruPenilaianController::class, 'saveAutoAssessment'])->name('penilaian.auto.save');
    
    // Auto Assessment with Criteria
    Route::get('penilaian/auto-criteria', [GuruPenilaianController::class, 'autoWithCriteria'])->name('penilaian.auto.criteria');
    Route::post('penilaian/auto-criteria/save', [GuruPenilaianController::class, 'saveAutoAssessmentWithCriteria'])->name('penilaian.auto.criteria.save');
    
    // Penilaian Export
    Route::get('penilaian/export', [GuruPenilaianController::class, 'export'])->name('penilaian.export');
    
    // Scoring Management (commented until controller is created)
    // Route::resource('scoring', GuruScoringController::class);
    Route::post('scoring/{practical}/{siswa}/auto', function () {
        abort(404);
    })->name('scoring.auto');

    Route::get('scoring/students/{student}', function () {
        abort(404);
    })->name('scoring.student');
    
    // Reports
    Route::get('reports', [GuruReportController::class, 'index'])->name('reports.index');
    Route::get('reports/attendance', [GuruReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('reports/practical', [GuruReportController::class, 'practical'])->name('reports.practical');
    Route::post('reports/generate', [GuruReportController::class, 'generate'])->name('reports.generate');
    
    // Laporan (Report) routes with Indonesian naming
    Route::get('laporan', [GuruReportController::class, 'index'])->name('laporan.index');
    
    // Absensi (Attendance) Reports
    Route::get('laporan/absensi', [GuruReportController::class, 'absensi'])->name('laporan.absensi');
    Route::get('laporan/absensi/bulanan', [GuruReportController::class, 'absensi'])->name('laporan.absensi.bulanan');
    Route::get('laporan/absensi/semester', [GuruReportController::class, 'absensi'])->name('laporan.absensi.semester');
    
    // Praktik reports
    Route::get('laporan/praktik', [GuruReportController::class, 'praktik'])->name('laporan.praktik');
    
    // Tugas (Assignment) Reports
    Route::get('laporan/tugas', [GuruReportController::class, 'tugas'])->name('laporan.tugas');
    Route::get('laporan/tugas/nilai', [GuruReportController::class, 'tugas'])->name('laporan.tugas.nilai');
    Route::get('laporan/tugas/terlambat', [GuruReportController::class, 'tugas'])->name('laporan.tugas.terlambat');
    
    // Nilai (Grade) Reports
    Route::get('laporan/nilai',          [GuruReportController::class, 'nilai'])->name('laporan.nilai');
    Route::get('laporan/nilai/mid',      [GuruReportController::class, 'nilai'])->name('laporan.nilai.mid');
    Route::get('laporan/nilai/semester', [GuruReportController::class, 'nilai'])->name('laporan.nilai.semester');
    
    // Siswa (Student) Reports
    Route::get('laporan/siswa',          [GuruReportController::class, 'siswa'])->name('laporan.siswa');
    Route::get('laporan/siswa/detail',   [GuruReportController::class, 'siswa'])->name('laporan.siswa.detail');
    Route::get('laporan/siswa/prestasi', [GuruReportController::class, 'siswa'])->name('laporan.siswa.prestasi');
    
    // Material Reports
    Route::get('laporan/materi', [GuruReportController::class, 'materi'])->name('laporan.materi');
    
    // Export and Generate
    Route::post('laporan/generate', [GuruReportController::class, 'generate'])->name('laporan.generate');
    
    // Profile
    Route::get('profile', [GuruProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [GuruProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/update-photo-url', [GuruProfileController::class, 'updatePhotoUrl'])->name('profile.update-photo-url');
    Route::post('profile/photo', [GuruProfileController::class, 'updatePhoto'])->name('profile.update-photo');
    Route::post('profile/remove-photo', [GuruProfileController::class, 'removePhoto'])->name('profile.remove-photo');
    Route::get('profile/change-password', [GuruProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('profile/change-password', [GuruProfileController::class, 'updatePassword'])->name('profile.change-password.post');

    // Jadwal Ujian (read-only)
    Route::get('jadwal-ujian', [\App\Http\Controllers\Guru\JadwalUjianController::class, 'index'])->name('jadwal-ujian.index');
});

// ✅ SISWA ROUTES - middleware digabung dalam array
Route::prefix('siswa')->name('siswa.')->middleware(['auth', 'siswa'])->group(function () {
    // Dashboard Siswa
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    
    // Pelajaran - Daftar mata pelajaran
    Route::get('/pelajaran', [\App\Http\Controllers\Siswa\PelajaranController::class, 'index'])->name('pelajaran.index');
    Route::get('/pelajaran/{id}', [\App\Http\Controllers\Siswa\PelajaranController::class, 'show'])->name('pelajaran.show');
    
    // Materi — CUSTOM ROUTES SEBELUM {material}
    Route::get('materials',                         [SiswaMaterialController::class, 'index'])->name('materials.index');
    Route::get('materials/search',                  [SiswaMaterialController::class, 'search'])->name('materials.search');
    Route::get('materials/history',                 [\App\Http\Controllers\Siswa\MaterialController::class, 'history'])->name('materials.history');
    Route::get('materials/health',                  [\App\Http\Controllers\Siswa\MaterialController::class, 'healthMaterials'])->name('materials.health');
    Route::get('materials/{material}',              [SiswaMaterialController::class, 'show'])->name('materials.show');
    Route::get('materials/{material}/download',     [SiswaMaterialController::class, 'download'])->name('materials.download');
    Route::post('materials/{material}/track-download', [SiswaMaterialController::class, 'trackDownload'])->name('materials.track-download');

    // Tugas — CUSTOM ROUTES SEBELUM {assignment}
    Route::get('assignments',                                                         [SiswaAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('assignments/export',                                                  [SiswaAssignmentController::class, 'export'])->name('assignments.export');
    Route::get('assignments/history',                                                 [\App\Http\Controllers\Siswa\AssignmentController::class, 'history'])->name('assignments.history');
    Route::get('assignments/archived',                                                [\App\Http\Controllers\Siswa\AssignmentController::class, 'archived'])->name('assignments.archived');
    Route::get('assignments/{assignment}',                                            [SiswaAssignmentController::class, 'show'])->name('assignments.show');
    Route::post('assignments/{assignment}/submit',                                    [SiswaAssignmentController::class, 'submit'])->name('assignments.submit');
    Route::get('assignments/{assignment}/submission',                                 [SiswaAssignmentController::class, 'submission'])->name('assignments.submission');
    Route::get('assignments/{assignment}/submission/download/{submission}',           [SiswaAssignmentController::class, 'downloadFile'])->name('assignments.download');

    // Praktikum — CUSTOM ROUTES SEBELUM {practical}
    Route::get('praktikum',          [SiswaPracticalController::class, 'index'])->name('praktikum.index');
    Route::get('praktikum/history',  [\App\Http\Controllers\Siswa\PracticalController::class, 'history'])->name('praktikum.history');
    Route::get('praktikum/progress', [\App\Http\Controllers\Siswa\PracticalController::class, 'getProgress'])->name('praktikum.progress');
    Route::get('praktikum/upcoming', [\App\Http\Controllers\Siswa\PracticalController::class, 'upcoming'])->name('praktikum.upcoming');
    Route::get('praktikum/export',   function () { abort(404); })->name('praktikum.export');
    Route::get('praktikum/{practical}',         [SiswaPracticalController::class, 'show'])->name('praktikum.show');
    Route::get('praktikum/{id}/scores',         [\App\Http\Controllers\Siswa\PracticalController::class, 'getScores'])->name('praktikum.scores');

    // Alias backward compat
    Route::get('practicals', [SiswaPracticalController::class, 'index'])->name('practicals.index');
    
    // Laporan Nilai - Siswa dapat melihat nilai praktikum dan absen
    Route::get('reports',              [SiswaScoreController::class, 'index'])->name('reports.index');
    Route::get('reports/praktikum',    [SiswaScoreController::class, 'practical'])->name('reports.practical');
    Route::get('reports/assignments',  [SiswaScoreController::class, 'assignment'])->name('reports.assignment');
    Route::get('reports/attendance',   [SiswaAttendanceController::class, 'index'])->name('reports.attendance');

    // Attendance — CUSTOM ROUTES SEBELUM {attendance}
    Route::get('attendance',           [SiswaAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('absensi',              [SiswaAttendanceController::class, 'index'])->name('absensi.index');
    Route::get('absensi/export',       [SiswaAttendanceController::class, 'export'])->name('absensi.export');
    Route::get('absensi/{attendance}', [SiswaAttendanceController::class, 'show'])->name('absensi.show');
    
    // Kelola Profil Siswa — CUSTOM ROUTES SEBELUM {profile}
    Route::get('profile',                 [\App\Http\Controllers\Siswa\ProfileController::class, 'index'])->name('profile.index');
    Route::get('profile/edit',            [SiswaProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile',                 [SiswaProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/update-photo-url',[SiswaProfileController::class, 'updatePhotoUrl'])->name('profile.update-photo-url');
    Route::get('profile/medical',  [\App\Http\Controllers\Siswa\ProfileController::class, 'medicalInfo'])->name('profile.medical');
    Route::post('profile/medical', [\App\Http\Controllers\Siswa\ProfileController::class, 'updateMedicalInfo'])->name('profile.medical.update');
    Route::post('profile/password',[SiswaProfileController::class, 'updatePassword'])->name('profile.password');

    // Nilai — CUSTOM ROUTES SEBELUM wildcard
    Route::get('nilai',            [SiswaScoreController::class, 'index'])->name('nilai.index');
    Route::get('nilai/export',     [SiswaScoreController::class, 'exportScores'])->name('nilai.export');
    Route::get('nilai/chart-data', [SiswaScoreController::class, 'getChartData'])->name('nilai.chart-data');

    // Jadwal Ujian (read-only, filtered by kelas siswa)
    Route::get('jadwal-ujian', [\App\Http\Controllers\Siswa\JadwalUjianController::class, 'index'])->name('jadwal-ujian.index');
});

// File Download Routes
Route::get('storage/{folder}/{filename}', [FileController::class, 'downloadFile'])
    ->where('filename', '.*')
    ->name('storage.file');

// Notification Routes (for all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('notifications/{notification}', [NotificationController::class, 'delete'])->name('notifications.delete');
});

// Exam Schedule Public Route (untuk guru dan siswa lihat detail jadwal)
Route::middleware('auth')->group(function () {
    Route::get('exam-schedules/{examSchedule}', function ($examSchedule) {
        $schedule = \App\Models\ExamSchedule::with(['subject', 'kelas'])->find($examSchedule);
        if (!$schedule) abort(404);
        return view('shared.jadwal-ujian.show', compact('schedule'));
    })->name('exam-schedules.show');
});

// Test routes untuk debugging - hanya di development
if (config('app.debug')) {
    require __DIR__ . '/test.php';
}

// Fallback Route
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
