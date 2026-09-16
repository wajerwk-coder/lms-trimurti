<?php
/**
 * Route debug sementara — HAPUS setelah selesai diagnostik!
 * Akses: GET /debug-kriteria-data (hanya bisa diakses admin yang login)
 */
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

if (config('app.debug') || app()->environment('production')) {
    Route::middleware(['auth'])->get('/debug-kriteria-data', function () {
        // Hanya admin
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $output = [];

        // 1. Semua praktikum
        $output['praktikum'] = DB::table('practicals')
            ->whereNull('deleted_at')
            ->select('id', 'title', 'subject_id', 'guru_id', 'kelas_id')
            ->orderBy('id')
            ->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'title'      => $p->title,
                'subject_id' => $p->subject_id,
                'guru_id'    => $p->guru_id,
                'kelas_id'   => $p->kelas_id,
            ]);

        // 2. Semua assessment_criteria dengan mata_praktik
        $output['kriteria'] = DB::table('assessment_criteria')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->select('id', 'mata_praktik', 'name', 'subject_id')
            ->orderBy('mata_praktik')
            ->orderBy('id')
            ->get()
            ->map(fn($k) => [
                'id'          => $k->id,
                'mata_praktik'=> $k->mata_praktik,
                'name'        => $k->name,
                'subject_id'  => $k->subject_id,
            ]);

        // 3. Untuk setiap praktikum, tunjukkan kriteria yang akan match
        $output['match_result'] = $output['praktikum']->map(function ($p) use ($output) {
            $title      = trim($p['title'] ?? '');
            $subjName   = '';

            // Ambil nama subject
            if ($p['subject_id']) {
                $subj     = DB::table('subjects')->where('id', $p['subject_id'])->first();
                $subjName = trim($subj?->name ?? '');
            }

            // Exact match judul
            $exactTitle = $output['kriteria']
                ->filter(fn($k) => $k['mata_praktik'] === $title)
                ->values();

            // Exact match subject
            $exactSubject = $output['kriteria']
                ->filter(fn($k) => $k['mata_praktik'] === $subjName && $subjName !== $title)
                ->values();

            return [
                'practical_id'   => $p['id'],
                'practical_title'=> $title,
                'subject_name'   => $subjName,
                'match_by_title' => $exactTitle,
                'match_by_subj'  => $exactSubject,
                'will_use'       => $exactTitle->isNotEmpty()
                    ? 'JUDUL: ' . $title
                    : ($exactSubject->isNotEmpty() ? 'SUBJECT: ' . $subjName : 'TIDAK ADA KRITERIA'),
            ];
        });

        return response()->json($output, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    });
}
