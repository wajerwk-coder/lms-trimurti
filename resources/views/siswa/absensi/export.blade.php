@extends('layouts.siswa')

@section('title', 'Export Absensi')
@section('page-title', 'Export Absensi')
@section('page-subtitle', 'Unduh rekap absensi dalam format CSV.')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold">
        Rekap Absensi —
        {{ \Carbon\Carbon::create(null, $month)->translatedFormat('F') }} {{ $year }}
    </h5>
    <button class="btn btn-outline-success btn-sm" onclick="window.print()">
        <i class="fas fa-print me-1"></i>Cetak
    </button>
</div>

{{-- Summary Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Hari Kerja', $stats['working_days'] ?? 0, 'secondary', 'calendar'],
        ['Hadir', $stats['present'] ?? 0, 'success', 'check-circle'],
        ['Izin/Sakit', $stats['permission'] ?? 0, 'info', 'info-circle'],
        ['Alpa', $stats['absent'] ?? 0, 'danger', 'times-circle'],
        ['Kehadiran', ($stats['attendance_rate'] ?? 0) . '%', 'primary', 'percentage'],
    ] as [$label, $value, $color, $icon])
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h4 fw-bold text-{{ $color }} mb-0">{{ $value }}</div>
                <small class="text-muted">{{ $label }}</small>
            </div>
        </div>
    @endforeach
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td class="ps-3 fw-semibold">
                                {{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}
                                <div><small class="text-muted">{{ \Carbon\Carbon::parse($attendance->date)->translatedFormat('l') }}</small></div>
                            </td>
                            <td class="text-muted">{{ $attendance->subject?->name ?? '—' }}</td>
                            <td class="text-center">
                                @php
                                    $st = $attendance->status;
                                    $badge = match($st) { 'hadir'=>'success','izin'=>'info','sakit'=>'warning','alpha'=>'danger', default=>'secondary' };
                                    $lbl   = match($st) { 'hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha', default=>ucfirst($st) };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ $lbl }}</span>
                            </td>
                            <td class="text-muted">{{ $attendance->note ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                Tidak ada data untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('css')
<style>
@media print {
    .sidebar, .top-header, .btn { display: none !important; }
    .main-content { margin: 0 !important; }
    .card { border: 1px solid #dee2e6 !important; }
}
</style>
@endpush

@endsection
