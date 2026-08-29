@extends('layouts.guru')

@section('title', 'Detail Praktikum — ' . $praktikum->title)
@section('page-title', $praktikum->title)
@section('page-subtitle', ($praktikum->subject?->name ?? '—') . ' · ' . ($praktikum->kelas?->name ?? 'Semua Kelas'))

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('guru.praktikum.edit', $praktikum) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <form action="{{ route('guru.praktikum.toggle-publish', $praktikum) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit"
                    class="btn btn-sm {{ $praktikum->is_published ? 'btn-secondary' : 'btn-success' }}">
                <i class="fas fa-{{ $praktikum->is_published ? 'eye-slash' : 'eye' }} me-1"></i>
                {{ $praktikum->is_published ? 'Sembunyikan' : 'Publikasikan' }}
            </button>
        </form>
        <a href="{{ route('guru.praktikum.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- Kolom Kiri --}}
    <div class="col-lg-8">

        {{-- Detail Praktikum --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-flask me-2 text-warning"></i>Detail Praktikum
                </h6>
                @if($praktikum->is_published)
                    <span class="badge bg-success">Dipublikasikan</span>
                @else
                    <span class="badge bg-warning text-dark">Draft</span>
                @endif
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small mb-1">Deskripsi</div>
                    <div class="bg-light rounded-2 p-3 small">{{ $praktikum->description }}</div>
                </div>

                @if($praktikum->instructions)
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Instruksi Detail</div>
                        <div class="bg-light rounded-2 p-3 small">
                            {!! nl2br(e($praktikum->instructions)) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Penilaian Terbaru --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-star me-2 text-warning"></i>Penilaian
                </h6>
                <span class="badge bg-primary bg-opacity-10 text-primary">
                    {{ $stats['total_scores'] }} penilaian
                </span>
            </div>
            <div class="card-body p-0">
                @if($praktikum->scores->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-star fa-2x opacity-25 mb-2 d-block"></i>
                        <small>Belum ada penilaian.</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Siswa</th>
                                    <th class="text-center">Nilai</th>
                                    <th>Feedback</th>
                                    <th>Dinilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($praktikum->scores as $score)
                                    <tr>
                                        <td class="ps-4 fw-semibold">
                                            {{ $score->siswa?->name ?? "Siswa #$score->siswa_id" }}
                                        </td>
                                        <td class="text-center">
                                            @if($score->score !== null)
                                                <span class="badge bg-{{ $score->score >= 75 ? 'success' : ($score->score >= 60 ? 'warning text-dark' : 'danger') }} fs-6">
                                                    {{ number_format($score->score, 1) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">—</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ Str::limit($score->feedback ?? '', 60) }}</td>
                                        <td class="text-muted">
                                            {{ $score->graded_at ? \Carbon\Carbon::parse($score->graded_at)->format('d/m/Y') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Kolom Kanan --}}
    <div class="col-lg-4">

        {{-- Stats --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-bar me-2 text-info"></i>Statistik
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    @foreach([
                        ['warning', $stats['total_scores'],  'Total Penilaian'],
                        ['success', $stats['graded_count'],  'Sudah Dinilai'],
                        ['info',    $stats['average_score'], 'Rata-rata Nilai'],
                    ] as [$color, $val, $label])
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-{{ $color }} bg-opacity-10">
                            <span class="small text-muted">{{ $label }}</span>
                            <span class="fw-bold text-{{ $color }}">{{ $val }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-info-circle me-2 text-secondary"></i>Info
                </h6>
            </div>
            <div class="card-body small">
                @foreach([
                    ['Mata Pelajaran', $praktikum->subject?->name ?? '—'],
                    ['Kelas',          $praktikum->kelas?->name ?? 'Semua Kelas'],
                    ['Batas Waktu',    $praktikum->due_date?->format('d/m/Y H:i') ?? '—'],
                    ['Status',         $praktikum->is_published ? 'Dipublikasikan' : 'Draft'],
                    ['Dibuat',         $praktikum->created_at->format('d M Y')],
                ] as [$label, $val])
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ $label }}</span>
                        <span class="fw-semibold text-end">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Aksi --}}
        <div class="d-flex flex-column gap-2">
            <a href="{{ route('guru.praktikum.edit', $praktikum) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i>Edit Praktikum
            </a>
            <form action="{{ route('guru.praktikum.destroy', $praktikum) }}" method="POST"
                  onsubmit="return confirm('Hapus praktikum \'{{ addslashes($praktikum->title) }}\'?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                    <i class="fas fa-trash me-1"></i>Hapus
                </button>
            </form>
            <a href="{{ route('guru.praktikum.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
            </a>
        </div>

    </div>
</div>

@endsection
