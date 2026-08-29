@extends('layouts.guru')

@section('title', 'Daftar Absensi')
@section('page-title', 'Absensi Siswa')
@section('page-subtitle', 'Kelola catatan kehadiran siswa.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('guru.absensi.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Tambah Absensi
        </a>
        <a href="{{ route('guru.absensi.bulk-create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-users me-1"></i>Absensi Massal
        </a>
    </div>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Total', 'value' => $stats['total'] ?? 0, 'color' => 'primary', 'icon' => 'list'],
        ['label' => 'Hadir', 'value' => $stats['hadir'] ?? 0, 'color' => 'success', 'icon' => 'check-circle'],
        ['label' => 'Izin', 'value' => $stats['izin'] ?? 0, 'color' => 'info', 'icon' => 'info-circle'],
        ['label' => 'Sakit', 'value' => $stats['sakit'] ?? 0, 'color' => 'warning', 'icon' => 'heartbeat'],
        ['label' => 'Alpha', 'value' => $stats['alpha'] ?? 0, 'color' => 'danger', 'icon' => 'times-circle'],
    ] as $s)
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-{{ $s['color'] }} bg-opacity-10">
                        <i class="fas fa-{{ $s['icon'] }} text-{{ $s['color'] }}"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 mb-0">{{ $s['value'] }}</div>
                        <small class="text-muted">{{ $s['label'] }}</small>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('guru.absensi.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Tanggal</label>
                <input type="date" name="date" class="form-control form-control-sm"
                       value="{{ $date ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Kelas</label>
                <select name="class" class="form-select form-select-sm">
                    <option value="all">Semua Kelas</option>
                    @foreach($classes ?? [] as $id => $name)
                        <option value="{{ $id }}" {{ ($class ?? 'all') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ ($type ?? '') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="izin" {{ ($type ?? '') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ ($type ?? '') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="alpha" {{ ($type ?? '') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('guru.absensi.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-sync"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-calendar-check me-2 text-primary"></i>Daftar Absensi</h6>
        <span class="badge bg-secondary">{{ method_exists($attendances, 'total') ? $attendances->total() : $attendances->count() }} data</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Siswa</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Status</th>
                        <th>Catatan</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $attendance->siswa?->name ?? '—' }}</div>
                                <small class="text-muted">{{ $attendance->siswa?->nis ?? '' }}</small>
                            </td>
                            <td class="text-muted">{{ $attendance->kelas?->name ?? '—' }}</td>
                            <td class="text-muted">
                                {{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}
                            </td>
                            <td>
                                @if($attendance->subject)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $attendance->subject->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $b = match($attendance->status) { 'hadir'=>'success','izin'=>'info','sakit'=>'warning','alpha'=>'danger', default=>'secondary' };
                                @endphp
                                <span class="badge bg-{{ $b }}">{{ ucfirst($attendance->status) }}</span>
                            </td>
                            <td class="text-muted small">{{ Str::limit($attendance->note ?? '', 30) ?: '—' }}</td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('guru.absensi.edit', $attendance) }}"
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="if(confirm('Hapus data ini?')) document.getElementById('del-{{ $attendance->id }}').submit()"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="del-{{ $attendance->id }}"
                                          action="{{ route('guru.absensi.destroy', $attendance) }}"
                                          method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">Belum ada data absensi</h6>
                                <a href="{{ route('guru.absensi.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Absensi
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($attendances) && method_exists($attendances, 'hasPages') && $attendances->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $attendances->links() }}
        </div>
    @endif
</div>

@endsection