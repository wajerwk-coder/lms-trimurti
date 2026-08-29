@extends('layouts.guru')

@section('title', 'Absensi Praktik - LMS Trimurti Husada')

@section('page-title', 'Absensi Praktik')
@section('page-subtitle', 'Kelola absensi praktik siswa')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('guru.absensi.index') }}" class="text-decoration-none">Absensi</a></li>
<li class="breadcrumb-item active" aria-current="page">Praktik</li>
@endsection

@section('page-actions')
<a href="{{ route('guru.absensi.create', ['type' => 'praktik']) }}" class="btn btn-primary">
    <i class="fas fa-plus me-2"></i>Buat Absensi Praktik
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h5 class="card-title mb-0">
                    <i class="fas fa-flask me-2 text-primary"></i>
                    Daftar Absensi Praktik
                </h5>
            </div>

            <div class="card-body">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $date ?? '' }}" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <select name="class" class="form-select" autocomplete="off">
                            <option value="all">Semua Kelas</option>
                            @foreach(($classes ?? []) as $classId => $className)
                                <option value="{{ $classId }}" {{ (string)($selectedClass ?? 'all') === (string)$classId ? 'selected' : '' }}>{{ $className }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Praktikum</label>
                        <select name="practical_id" class="form-select" autocomplete="off">
                            <option value="">Semua Praktikum</option>
                            @foreach(($practicals ?? []) as $practical)
                                <option value="{{ $practical->id }}" {{ (string)($practical_id ?? '') === (string)$practical->id ? 'selected' : '' }}>
                                    {{ $practical->title ?? ('Praktikum #' . $practical->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card text-center bg-success bg-opacity-10 border border-success border-opacity-25">
                            <div class="h3 mb-1 text-success">{{ $statusCounts['hadir'] ?? 0 }}</div>
                            <div class="small text-success fw-medium">Hadir</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card text-center bg-info bg-opacity-10 border border-info border-opacity-25">
                            <div class="h3 mb-1 text-info">{{ $statusCounts['izin'] ?? 0 }}</div>
                            <div class="small text-info fw-medium">Izin</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card text-center bg-warning bg-opacity-10 border border-warning border-opacity-25">
                            <div class="h3 mb-1 text-warning">{{ $statusCounts['sakit'] ?? 0 }}</div>
                            <div class="small text-warning fw-medium">Sakit</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card text-center bg-danger bg-opacity-10 border border-danger border-opacity-25">
                            <div class="h3 mb-1 text-danger">{{ $statusCounts['alpha'] ?? 0 }}</div>
                            <div class="small text-danger fw-medium">Alpha</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Waktu</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($attendances ?? []) as $attendance)
                                <tr>
                                    <td>{{ $attendance->siswa?->name ?? '-' }}</td>
                                    <td>{{ $attendance->siswa?->kelas?->name ?? '-' }}</td>
                                    <td>{{ $attendance->date?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $attendance->status_color ?? 'secondary' }}">{{ ucfirst($attendance->status ?? '-') }}</span>
                                    </td>
                                    <td>
                                        @if(null && null)
                                            {{ null->format('H:i') }} - {{ null->format('H:i') }}
                                        @elseif(null)
                                            {{ null->format('H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $attendance->note ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('guru.absensi.edit', $attendance->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">Belum ada absensi praktik.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists(($attendances ?? null), 'links'))
                    <div class="mt-3">
                        {{ $attendances->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection