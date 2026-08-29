@extends('layouts.siswa')

@section('title', 'Detail Nilai Praktikum')
@section('page-title', 'Detail Nilai Praktikum')
@section('page-subtitle', optional($score->practical)->title ?? '—')

@section('page-actions')
    <a href="{{ route('siswa.nilai.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-star me-2 text-warning"></i>Nilai Praktikum
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    @foreach([
                        ['Praktikum',      optional($score->practical)->title ?? '—'],
                        ['Kriteria',       optional($score->criteria)->name ?? '—'],
                        ['Rata-rata Kelas', number_format($averageScore, 2)],
                        ['Tanggal Dinilai', optional($score->created_at)->format('d M Y') ?? '—'],
                    ] as [$label, $val])
                    <div class="col-md-6">
                        <div class="text-muted" style="font-size:.72rem;">{{ $label }}</div>
                        <div class="fw-semibold text-dark">{{ $val }}</div>
                    </div>
                    @endforeach
                </div>

                <hr class="my-4">

                <div class="text-center">
                    @php
                        $s = $score?->score ?? 0;
                        $c = $s >= 90 ? 'success' : ($s >= 80 ? 'primary' : ($s >= 70 ? 'warning' : 'danger'));
                        $g = $s >= 90 ? 'A' : ($s >= 80 ? 'B' : ($s >= 70 ? 'C' : ($s >= 60 ? 'D' : 'E')));
                    @endphp
                    <div class="display-3 fw-bold text-{{ $c }}">{{ $s }}</div>
                    <div class="text-muted small">dari 100</div>
                    <span class="badge bg-{{ $c }} px-3 py-2 fs-5 mt-2">Grade {{ $g }}</span>

                    @if($score?->feedback)
                    <div class="mt-4 text-start">
                        <div class="text-muted small fw-semibold mb-1">Catatan Guru:</div>
                        <div class="bg-light rounded-2 p-3 small">{{ $score->feedback }}</div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-white border-top py-3">
                <a href="{{ route('siswa.reports.practical') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection