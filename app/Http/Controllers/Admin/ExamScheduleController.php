<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\UserCentral;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ── Index ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $schedules = ExamSchedule::with(['subject', 'kelas', 'creator'])
            ->when($request->get('search'), function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->get('exam_type'), fn($q, $t) => $q->where('exam_type', $t))
            ->when($request->get('kelas_id'), fn($q, $k) => $q->where('kelas_id', $k))
            ->latest()
            ->paginate(10);

        $kelas = \App\Models\Kelas::orderBy('name')->get();

        return view('admin.exam-schedules.index', compact('schedules', 'kelas'));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.exam-schedules.create', [
            'subjects' => \App\Models\Subject::where('is_active', true)->orderBy('name')->get(),
            'kelas'    => \App\Models\Kelas::orderBy('name')->get(),
        ]);
    }

    // ── Store ─────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'exam_type'        => 'required|in:uts,uas,quiz,praktikum,lainnya',
            'subject_id'       => 'required|exists:subjects,id',
            'kelas_id'         => 'nullable|exists:classes,id',
            'start_time'       => 'required|date',
            'end_time'         => 'required|date|after:start_time',
            'location'         => 'nullable|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
        ], [
            'title.required'       => 'Judul wajib diisi.',
            'subject_id.required'  => 'Mata pelajaran wajib dipilih.',
            'start_time.required'  => 'Waktu mulai wajib diisi.',
            'end_time.after'       => 'Waktu selesai harus setelah waktu mulai.',
            'duration_minutes.min' => 'Durasi minimal 1 menit.',
        ]);

        try {
            DB::beginTransaction();

            $isPublished = $request->boolean('is_published');

            $schedule = ExamSchedule::create([
                'title'            => $request->title,
                'description'      => $request->description,
                'exam_type'        => $request->exam_type,
                'subject_id'       => $request->subject_id,
                'kelas_id'         => $request->kelas_id,
                'created_by'       => auth()->id(),
                'start_time'       => $request->start_time,
                'end_time'         => $request->end_time,
                'location'         => $request->location,
                'duration_minutes' => $request->duration_minutes,
                'is_published'     => $isPublished,
            ]);

            if ($isPublished) {
                try {
                    $this->sendExamNotifications($schedule);
                } catch (\Exception $e) {
                    Log::warning('Notification failed but schedule created: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()->route('admin.exam-schedules.index')
                ->with('success', "Jadwal '{$schedule->title}' berhasil dibuat."
                    . ($isPublished ? ' Notifikasi telah dikirim.' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating exam schedule: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat jadwal: ' . $e->getMessage());
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function show(ExamSchedule $examSchedule)
    {
        $examSchedule->load(['subject', 'kelas', 'creator']);
        return view('admin.exam-schedules.show', compact('examSchedule'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(ExamSchedule $examSchedule)
    {
        return view('admin.exam-schedules.edit', [
            'examSchedule' => $examSchedule,
            'subjects'     => \App\Models\Subject::where('is_active', true)->orderBy('name')->get(),
            'kelas'        => \App\Models\Kelas::orderBy('name')->get(),
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, ExamSchedule $examSchedule): RedirectResponse
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'exam_type'        => 'required|in:uts,uas,quiz,praktikum,lainnya',
            'subject_id'       => 'required|exists:subjects,id',
            'kelas_id'         => 'nullable|exists:classes,id',
            'start_time'       => 'required|date',
            'end_time'         => 'required|date|after:start_time',
            'location'         => 'nullable|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
        ], [
            'end_time.after'   => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        try {
            DB::beginTransaction();

            $wasPublished = $examSchedule->is_published;
            $isPublished  = $request->boolean('is_published');

            $examSchedule->update([
                'title'            => $request->title,
                'description'      => $request->description,
                'exam_type'        => $request->exam_type,
                'subject_id'       => $request->subject_id,
                'kelas_id'         => $request->kelas_id,
                'start_time'       => $request->start_time,
                'end_time'         => $request->end_time,
                'location'         => $request->location,
                'duration_minutes' => $request->duration_minutes,
                'is_published'     => $isPublished,
            ]);

            // Kirim notifikasi hanya jika baru dipublikasikan
            if (!$wasPublished && $isPublished) {
                try {
                    $this->sendExamNotifications($examSchedule);
                } catch (\Exception $e) {
                    Log::warning('Notification failed during update: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()->route('admin.exam-schedules.index')
                ->with('success', "Jadwal '{$examSchedule->title}' berhasil diperbarui."
                    . (!$wasPublished && $isPublished ? ' Notifikasi telah dikirim.' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating exam schedule: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui jadwal: ' . $e->getMessage());
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(ExamSchedule $examSchedule): RedirectResponse
    {
        try {
            $nama = $examSchedule->title;
            $examSchedule->delete();

            return redirect()->route('admin.exam-schedules.index')
                ->with('success', "Jadwal '{$nama}' berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Error deleting exam schedule: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus jadwal: ' . $e->getMessage());
        }
    }

    // ── Publish ───────────────────────────────────────────────────────────

    public function publish(ExamSchedule $examSchedule): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $examSchedule->update(['is_published' => true]);

            try {
                $this->sendExamNotifications($examSchedule);
            } catch (\Exception $e) {
                Log::warning('Notification failed during publish: ' . $e->getMessage());
            }

            DB::commit();

            return back()->with('success', "Jadwal '{$examSchedule->title}' berhasil dipublikasikan dan notifikasi dikirim.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error publishing exam schedule: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mempublikasikan jadwal: ' . $e->getMessage());
        }
    }

    // ── Private: Send Notifications ───────────────────────────────────────

    private function sendExamNotifications(ExamSchedule $schedule): void
    {
        $schedule->load(['subject', 'kelas']);

        $examTypeLabels = [
            'uts'       => 'UTS',
            'uas'       => 'UAS',
            'quiz'      => 'Quiz',
            'praktikum' => 'Praktikum',
            'lainnya'   => 'Ujian',
        ];

        $subjectName = $schedule->subject?->name ?? 'Mata Pelajaran';
        $typeLabel   = $examTypeLabels[$schedule->exam_type] ?? strtoupper($schedule->exam_type);
        $judulNotif  = "Jadwal {$typeLabel}: {$schedule->title}";
        $pesanNotif  = "Jadwal {$typeLabel} {$subjectName} akan dilaksanakan pada "
                     . $schedule->start_time->translatedFormat('l, d F Y')
                     . " pukul " . $schedule->start_time->format('H:i') . " WIB"
                     . ($schedule->location ? " di {$schedule->location}" : "")
                     . ". Durasi: {$schedule->duration_minutes} menit.";

        $adminId = auth()->id();
        $dataJson = json_encode([
            'exam_schedule_id' => $schedule->id,
            'exam_type'        => $schedule->exam_type,
            'subject'          => $subjectName,
            'start_time'       => $schedule->start_time->toISOString(),
            'location'         => $schedule->location,
        ]);

        // ── URL per role ────────────────────────────────────────────────────
        try {
            $urlSiswa = route('siswa.jadwal-ujian.index');
        } catch (\Exception $e) {
            $urlSiswa = '/siswa/jadwal-ujian';
        }
        try {
            $urlGuru = route('guru.jadwal-ujian.index');
        } catch (\Exception $e) {
            $urlGuru = '/guru/jadwal-ujian';
        }

        // ── Kumpulkan penerima ──────────────────────────────────────────────
        $siswaUserIds = collect();
        $guruUserIds  = collect();

        if ($schedule->kelas_id) {
            // Siswa di kelas terpilih
            $siswaUserIds = Siswa::where('kelas_id', $schedule->kelas_id)
                ->whereNull('deleted_at')
                ->pluck('user_id');

            // Guru yang mengajar di kelas ini:
            // 1. Via class_subjects.teacher_id (jadwal mengajar)
            $guruViaClassSubjects = DB::table('class_subjects')
                ->where('class_id', $schedule->kelas_id)
                ->whereNotNull('teacher_id')
                ->pluck('teacher_id');

            // 2. Via subjects.guru_id (mata pelajaran yang di-assign ke kelas ini)
            $guruViaSubject = DB::table('subjects')
                ->where('kelas_id', $schedule->kelas_id)
                ->whereNotNull('guru_id')
                ->pluck('guru_id');

            // 3. Via guru_subjects → ambil semua guru yang punya subject di kelas ini
            $guruViaGuru = DB::table('guru_subjects')
                ->join('gurus', 'guru_subjects.guru_id', '=', 'gurus.id')
                ->join('subjects', 'guru_subjects.subject_id', '=', 'subjects.id')
                ->where('subjects.kelas_id', $schedule->kelas_id)
                ->pluck('gurus.user_id');

            $guruUserIds = $guruViaClassSubjects
                ->merge($guruViaSubject)
                ->merge($guruViaGuru)
                ->unique()
                ->filter();

            // Jika tidak ada guru spesifik, kirim ke semua guru aktif
            if ($guruUserIds->isEmpty()) {
                $guruUserIds = UserCentral::where('role', 'guru')
                    ->where('is_active', true)
                    ->pluck('id');
            }
        } else {
            // Tanpa kelas → kirim ke semua siswa dan semua guru
            $siswaUserIds = Siswa::whereNull('deleted_at')->pluck('user_id');
            $guruUserIds  = UserCentral::where('role', 'guru')
                ->where('is_active', true)
                ->pluck('id');
        }

        $siswaUserIds = $siswaUserIds->filter()->unique()->values();
        $guruUserIds  = $guruUserIds->filter()->unique()->values();

        $now = now();

        // ── Insert notifikasi siswa ─────────────────────────────────────────
        foreach ($siswaUserIds as $userId) {
            DB::table('notifications')->insert([
                'penerima_id'      => $userId,
                'receiver_id'      => $userId,
                'receiver_type'    => 'siswa',
                'sender_id'        => $adminId,
                'created_by'       => $adminId,
                'title'            => $judulNotif,
                'judul'            => $judulNotif,
                'message'          => $pesanNotif,
                'pesan'            => $pesanNotif,
                'type'             => 'exam_schedule',
                'tipe'             => 'exam_schedule',
                'tipe_notifikasi'  => 'exam_schedule',
                'tipe_penerima'    => 'user',
                'action_url'       => $urlSiswa,
                'url_aksi'         => $urlSiswa,
                'is_read'          => false,
                'status'           => 'belum_dibaca',
                'prioritas'        => 'tinggi',
                'data'             => $dataJson,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── Insert notifikasi guru ──────────────────────────────────────────
        foreach ($guruUserIds as $userId) {
            DB::table('notifications')->insert([
                'penerima_id'      => $userId,
                'receiver_id'      => $userId,
                'receiver_type'    => 'guru',
                'sender_id'        => $adminId,
                'created_by'       => $adminId,
                'title'            => $judulNotif,
                'judul'            => $judulNotif,
                'message'          => $pesanNotif,
                'pesan'            => $pesanNotif,
                'type'             => 'exam_schedule',
                'tipe'             => 'exam_schedule',
                'tipe_notifikasi'  => 'exam_schedule',
                'tipe_penerima'    => 'user',
                'action_url'       => $urlGuru,
                'url_aksi'         => $urlGuru,
                'is_read'          => false,
                'status'           => 'belum_dibaca',
                'prioritas'        => 'tinggi',
                'data'             => $dataJson,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        $totalPenerima = $siswaUserIds->count() + $guruUserIds->count();
        Log::info("Exam schedule notifications sent", [
            'schedule_id' => $schedule->id,
            'title'       => $schedule->title,
            'siswa'       => $siswaUserIds->count(),
            'guru'        => $guruUserIds->count(),
            'total'       => $totalPenerima,
        ]);
    }
}
