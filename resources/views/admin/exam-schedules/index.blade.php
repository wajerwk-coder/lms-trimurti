@extends('layouts.admin')

@section('title', 'Jadwal Ujian')
@section('page-title', 'Jadwal Ujian')
@section('page-subtitle', 'Buat dan kelola jadwal ujian dengan notifikasi otomatis')

@section('page-actions')
    <a href="{{ route('admin.exam-schedules.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Buat Jadwal Baru
    </a>
@endsection

@section('content')
<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats --}}
    @php
        $totalJadwal    = $schedules->total();
        $totalPublished = $schedules->getCollection()->where('is_published', true)->count();
        $totalDraft     = $schedules->getCollection()->where('is_published', false)->count();
        $upcoming       = $schedules->getCollection()->filter(fn($s) => $s->start_time && \Carbon\Carbon::parse($s->start_time)->isFuture())->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-calendar-alt text-primary fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $totalJadwal }}</div>
                        <small class="text-muted">Total Jadwal</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-bell text-success fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $totalPublished }}</div>
                        <small class="text-muted">Dipublikasikan</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-warning bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-file-alt text-warning fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $totalDraft }}</div>
                        <small class="text-muted">Draft</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-info bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-hourglass-half text-info fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $upcoming }}</div>
                        <small class="text-muted">Akan Datang</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.exam-schedules.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-6 col-md-4">
                        <label class="form-label small fw-semibold mb-1">Cari Jadwal</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Judul atau deskripsi..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label small fw-semibold mb-1">Tipe Ujian</label>
                        <select name="exam_type" class="form-select form-select-sm">
                            <option value="">Semua Tipe</option>
                            @foreach(['uts'=>'UTS','uas'=>'UAS','quiz'=>'Quiz','praktikum'=>'Praktikum','lainnya'=>'Lainnya'] as $val => $lbl)
                                <option value="{{ $val }}" {{ request('exam_type') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label small fw-semibold mb-1">Kelas</label>
                        <select name="kelas_id" class="form-select form-select-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.exam-schedules.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>Daftar Jadwal Ujian
            </h6>
            <span class="badge bg-secondary">{{ $schedules->total() }} jadwal</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Judul Ujian</th>
                            <th class="text-center">Tipe</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Jadwal</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                        @php
                            $typeColors = [
                                'uts'       => 'info',
                                'uas'       => 'danger',
                                'quiz'      => 'warning',
                                'praktikum' => 'success',
                                'lainnya'   => 'secondary',
                            ];
                            $tColor = $typeColors[$schedule->exam_type] ?? 'secondary';

                            // Status
                            $now = now();
                            $start = $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time) : null;
                            $end   = $schedule->end_time   ? \Carbon\Carbon::parse($schedule->end_time)   : null;
                            if (!$schedule->is_published) {
                                $statusLabel = 'Draft';
                                $statusColor = 'secondary';
                            } elseif ($start && $start->isFuture()) {
                                $statusLabel = 'Akan Datang';
                                $statusColor = 'info';
                            } elseif ($start && $end && $now->between($start, $end)) {
                                $statusLabel = 'Berlangsung';
                                $statusColor = 'success';
                            } else {
                                $statusLabel = 'Selesai';
                                $statusColor = 'secondary';
                            }
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 bg-{{ $tColor }} bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:34px;height:34px;">
                                        <i class="fas fa-file-alt text-{{ $tColor }} fa-sm"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $schedule->title }}</div>
                                        @if($schedule->description)
                                            <small class="text-muted">{{ Str::limit($schedule->description, 55) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $tColor }} bg-opacity-15 text-{{ $tColor }} fw-semibold">
                                    {{ strtoupper($schedule->exam_type) }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $schedule->subject?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $schedule->kelas?->name ?? 'Semua Kelas' }}</td>
                            <td>
                                @if($start)
                                <div class="small">
                                    <div><i class="fas fa-calendar me-1 text-muted"></i>{{ $start->format('d M Y') }}</div>
                                    <div class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $start->format('H:i') }}
                                        @if($end) — {{ $end->format('H:i') }} @endif
                                    </div>
                                    @if($schedule->location)
                                        <div class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $schedule->location }}
                                        </div>
                                    @endif
                                </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColor }} bg-opacity-15 text-{{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.exam-schedules.show', $schedule) }}"
                                       class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.exam-schedules.edit', $schedule) }}"
                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$schedule->is_published)
                                    <form method="POST"
                                          action="{{ route('admin.exam-schedules.publish', $schedule) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Publikasikan jadwal ini? Notifikasi akan dikirim ke guru dan siswa.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Publikasikan">
                                            <i class="fas fa-bell"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST"
                                          action="{{ route('admin.exam-schedules.destroy', $schedule) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h6 class="text-muted">Belum ada jadwal ujian</h6>
                                <a href="{{ route('admin.exam-schedules.create') }}"
                                   class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Buat Jadwal Baru
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($schedules->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $schedules->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
