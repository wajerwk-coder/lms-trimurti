@extends('layouts.guru')

@section('title', 'Laporan')
@section('page-title', 'Laporan')
@section('page-subtitle', 'Ringkasan dan akses semua laporan mengajar.')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Laporan</li>
@endsection

@push('css')
<style>
.lap-nav-card {
    border: 1px solid #e8edf2 !important;
    border-radius: 14px !important;
    transition: transform .18s, box-shadow .18s, border-color .18s;
    text-decoration: none !important;
}
.lap-nav-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,.09) !important;
    border-color: #bae6fd !important;
}
.lap-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: #fff; flex-shrink: 0;
}
.lap-link {
    display: flex; align-items: center; gap: .4rem;
    font-size: .8rem; font-weight: 500; color: #3b82f6;
    text-decoration: none; padding: .2rem 0;
    transition: color .12s, transform .1s;
}
.lap-link:hover { color: #1d4ed8; transform: translateX(3px); }
.lap-link i { font-size: .65rem; }

.stat-box {
    border-radius: 12px; padding: 1rem 1.25rem;
    border: 1px solid #e8edf2;
    transition: transform .15s;
}
.stat-box:hover { transform: translateY(-2px); }
</style>
@endpush

@section('content')

{{-- ══ STATS CEPAT ════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @foreach([
        ['from'=>'#0891b2','to'=>'#0e7490','icon'=>'fa-users',          'val'=>$stats['total_students']       ?? 0, 'label'=>'Total Siswa',         'sub'=>'Yang kamu ajar'],
        ['from'=>'#16a34a','to'=>'#15803d','icon'=>'fa-calendar-check', 'val'=>($stats['attendance_rate']     ?? 0).'%','label'=>'Rata-rata Kehadiran','sub'=>'Bulan ini'],
        ['from'=>'#d97706','to'=>'#b45309','icon'=>'fa-star',           'val'=>$stats['average_score']        ?? 0, 'label'=>'Rata-rata Nilai',      'sub'=>'Semua penilaian'],
        ['from'=>'#7c3aed','to'=>'#6d28d9','icon'=>'fa-check-circle',   'val'=>$stats['completed_assignments'] ?? 0,'label'=>'Tugas Selesai',        'sub'=>'Sudah dinilai'],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="stat-box d-flex align-items-center gap-3 bg-white shadow-sm">
            <div class="lap-icon flex-shrink-0"
                 style="background:linear-gradient(135deg,{{ $s['from'] }},{{ $s['to'] }});">
                <i class="fas {{ $s['icon'] }}"></i>
            </div>
            <div>
                <div class="fw-black text-dark" style="font-size:1.55rem;line-height:1;letter-spacing:-.5px;">
                    {{ $s['val'] }}
                </div>
                <div class="fw-semibold text-dark" style="font-size:.8rem;">{{ $s['label'] }}</div>
                <div class="text-muted" style="font-size:.7rem;">{{ $s['sub'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ NAVIGASI LAPORAN ═══════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Absensi --}}
    <div class="col-md-6 col-lg-4">
        <div class="lap-nav-card card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="lap-icon" style="background:linear-gradient(135deg,#0891b2,#0e7490);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:.95rem;">Laporan Absensi</div>
                        <div class="text-muted" style="font-size:.75rem;">Kehadiran siswa per mata pelajaran</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('guru.laporan.absensi') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Rekap Absensi
                    </a>
                    <a href="{{ route('guru.laporan.absensi.bulanan') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Absensi Bulanan
                    </a>
                    <a href="{{ route('guru.laporan.absensi.semester') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Absensi Semester
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tugas --}}
    <div class="col-md-6 col-lg-4">
        <div class="lap-nav-card card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="lap-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:.95rem;">Laporan Tugas</div>
                        <div class="text-muted" style="font-size:.75rem;">Penyelesaian dan penilaian tugas</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('guru.laporan.tugas') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Rekap Tugas
                    </a>
                    <a href="{{ route('guru.laporan.tugas.nilai') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Nilai Tugas
                    </a>
                    <a href="{{ route('guru.laporan.tugas.terlambat') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Keterlambatan
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Praktikum --}}
    <div class="col-md-6 col-lg-4">
        <div class="lap-nav-card card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="lap-icon" style="background:linear-gradient(135deg,#d97706,#b45309);">
                        <i class="fas fa-flask"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:.95rem;">Laporan Praktikum</div>
                        <div class="text-muted" style="font-size:.75rem;">Kegiatan praktikum dan penilaian</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('guru.laporan.praktik') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Rekap Praktikum
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Nilai --}}
    <div class="col-md-6 col-lg-4">
        <div class="lap-nav-card card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="lap-icon" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:.95rem;">Laporan Nilai</div>
                        <div class="text-muted" style="font-size:.75rem;">Rekap nilai tugas dan praktikum</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('guru.laporan.nilai') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Rekap Nilai
                    </a>
                    <a href="{{ route('guru.laporan.nilai.mid') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Nilai MID
                    </a>
                    <a href="{{ route('guru.laporan.nilai.semester') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Nilai Semester
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Siswa --}}
    <div class="col-md-6 col-lg-4">
        <div class="lap-nav-card card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="lap-icon" style="background:linear-gradient(135deg,#db2777,#be185d);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:.95rem;">Laporan Siswa</div>
                        <div class="text-muted" style="font-size:.75rem;">Perkembangan individual siswa</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('guru.laporan.siswa') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Rekap Siswa
                    </a>
                    <a href="{{ route('guru.laporan.siswa.detail') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Detail Siswa
                    </a>
                    <a href="{{ route('guru.laporan.siswa.prestasi') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Prestasi Siswa
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Materi --}}
    <div class="col-md-6 col-lg-4">
        <div class="lap-nav-card card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="lap-icon" style="background:linear-gradient(135deg,#16a34a,#15803d);">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:.95rem;">Laporan Materi</div>
                        <div class="text-muted" style="font-size:.75rem;">Statistik materi dan unduhan</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('guru.laporan.materi') }}" class="lap-link">
                        <i class="fas fa-chevron-right"></i> Rekap Materi
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══ EXPORT PDF ══════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:14px 14px 0 0;">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-file-pdf me-2 text-danger"></i>Ekspor Laporan ke PDF
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('guru.laporan.generate') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Jenis Laporan</label>
                    <select name="type" class="form-select" required>
                        <option value="">Pilih Laporan</option>
                        <option value="absensi">Absensi</option>
                        <option value="tugas">Tugas</option>
                        <option value="praktik">Praktikum</option>
                        <option value="materi">Materi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control"
                           value="{{ now()->subMonth()->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control"
                           value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach(\App\Models\Kelas::orderBy('name')->get() as $k)
                            <option value="{{ $k->id }}">{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="hidden" name="format" value="pdf">
                    <button type="submit" class="btn btn-danger w-100" style="border-radius:8px;">
                        <i class="fas fa-file-pdf me-2"></i>Ekspor PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
