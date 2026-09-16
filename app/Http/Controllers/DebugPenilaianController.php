<?php
/**
 * Controller debug sementara — HAPUS setelah masalah teridentifikasi
 * Akses: GET /debug-penilaian?practical_id=ID
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\KriteriaPenilaian;

class DebugPenilaianController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $practicalId = $request->input('practical_id');

        // Semua praktikum
        $practicals = DB::table('practicals as p')
            ->leftJoin('subjects as s', 's.id', '=', 'p.subject_id')
            ->select('p.id', 'p.title', 'p.subject_id', 's.name as subject_name', 'p.guru_id', 'p.kelas_id')
            ->whereNull('p.deleted_at')
            ->orderBy('p.id')
            ->get();

        // Semua mata_praktik unik
        $mataPraktiks = KriteriaPenilaian::active()
            ->whereNotNull('mata_praktik')
            ->where('mata_praktik', '!=', '')
            ->distinct()
            ->pluck('mata_praktik');

        // Semua kriteria
        $semuaKriteria = KriteriaPenilaian::active()
            ->select('id', 'name', 'mata_praktik', 'kategori', 'weight')
            ->orderBy('mata_praktik')
            ->orderBy('kategori')
            ->get();

        // Simulasi pencarian untuk practical_id yang dipilih
        $simulasi = null;
        if ($practicalId) {
            $practical = DB::table('practicals as p')
                ->leftJoin('subjects as s', 's.id', '=', 'p.subject_id')
                ->select('p.id', 'p.title', 'p.subject_id', 's.name as subject_name')
                ->where('p.id', $practicalId)
                ->whereNull('p.deleted_at')
                ->first();

            if ($practical) {
                $title   = trim($practical->title ?? '');
                $subject = trim($practical->subject_name ?? '');

                $exactTitle   = KriteriaPenilaian::active()->where('mata_praktik', $title)->get();
                $exactSubject = $subject !== $title
                    ? KriteriaPenilaian::active()->where('mata_praktik', $subject)->get()
                    : collect();

                $simulasi = [
                    'practical'     => $practical,
                    'title_used'    => $title,
                    'subject_used'  => $subject,
                    'exact_title'   => $exactTitle,
                    'exact_subject' => $exactSubject,
                    'result'        => $exactTitle->isNotEmpty()
                        ? 'PAKAI exact judul'
                        : ($exactSubject->isNotEmpty() ? 'PAKAI exact subject' : 'TIDAK ADA KRITERIA'),
                ];
            }
        }

        return response()->json([
            'praktikum'      => $practicals,
            'mata_praktiks'  => $mataPraktiks,
            'semua_kriteria' => $semuaKriteria,
            'simulasi'       => $simulasi,
        ], 200, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
