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
        $upcoming       = $schedules->getCollection()->filter(
            fn($s) => $s->start_time && \Carbon\Carbon::parse($s->start_time)->isFuture()
        )->count();
    @endphp
    <div class="row g-3 mb-4">
        @foreach([
            ['primary', 'fa-clipboard-list',  $totalJadwal,    'Total Jadwal'],
            ['success', 'fa-bell',             $totalPublished, 'Dipublikasikan'],
            ['warning', 'fa-file-alt',         $totalDraft,     'Draft'],
            ['info',    'fa-hourglass-half',   $upcoming,       'Akan Datang'],
        ] as [$color, $icon, $val, $label])
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 p-2 bg-{{ $color }} bg-opacity-10 flex-shrink-0">
                        <i class="fas {{ $icon }} text-{{ $color }} fa-lg"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $val }}</div>
                        <small class="text-muted">{{ $label }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.exam-schedules.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Cari Jadwal</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Judul atau deskripsi..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-md-3 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.exam-schedules.index') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-undo"></i>
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
                <i class="fas fa-clipboard-list me-2 text-primary"></i>Daftar Jadwal Ujian
            </h6>
            <span class="badge bg-secondary" id="jadwalCount">{{ $schedules->total() }} jadwal</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle small" id="jadwalTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Judul Jadwal</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Tanggal & Waktu</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                        @php
                            $now   = now();
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

                            $isToday = $start && $start->isToday();
                        @endphp
                        <tr>
                            {{-- Judul --}}
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 bg-success bg-opacity-10 d-flex align-items-center
                                                justify-content-center flex-shrink-0"
                                         style="width:38px;height:38px;">
                                        <i class="fas fa-flask text-success fa-sm"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $schedule->title }}</div>
                                        @if($schedule->description)
                                            <small class="text-muted">
                                                {{ Str::limit($schedule->description, 50) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Mata Pelajaran --}}
                            <td>
                                @if($schedule->subject)
                                    <span class="fw-semibold small">{{ $schedule->subject->name }}</span>
                                    @if($schedule->subject->code)
                                        <br><small class="text-muted">{{ $schedule->subject->code }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Kelas --}}
                            <td>
                                @if($schedule->kelas)
                                    <span class="fw-semibold small">{{ $schedule->kelas->name }}</span>
                                    @if($schedule->kelas->grade)
                                        <br><small class="text-muted">Kelas {{ $schedule->kelas->grade }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary small">
                                        Semua Kelas
                                    </span>
                                @endif
                            </td>

                            {{-- Tanggal & Waktu --}}
                            <td>
                                @if($start)
                                <div class="small lh-sm">
                                    <div class="fw-semibold {{ $isToday ? 'text-success' : '' }}">
                                        <i class="fas fa-calendar-day me-1 text-muted"></i>
                                        {{ $start->translatedFormat('d M Y') }}
                                        @if($isToday)
                                            <span class="badge bg-success ms-1" style="font-size:.6rem;">Hari ini</span>
                                        @endif
                                    </div>
                                    <div class="text-muted mt-1">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $start->format('H:i') }}
                                        @if($end) — {{ $end->format('H:i') }} WIT @endif
                                    </div>
                                    @if($schedule->location)
                                        <div class="text-muted mt-1">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $schedule->location }}
                                        </div>
                                    @endif
                                </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Durasi --}}
                            <td class="text-center">
                                @if($schedule->duration_minutes)
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-stopwatch me-1 text-muted"></i>
                                        {{ $schedule->duration_minutes }} mnt
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @php
                                    $dot = match($statusColor) {
                                        'success'   => '🟢',
                                        'info'      => '🔵',
                                        'secondary' => $schedule->is_published ? '⚫' : '⚪',
                                        default     => '',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}
                                             border border-{{ $statusColor }} border-opacity-25">
                                    @if($statusLabel === 'Berlangsung')
                                        <span class="me-1" style="font-size:.55rem;vertical-align:middle;">●</span>
                                    @endif
                                    {{ $statusLabel }}
                                </span>
                                @if($schedule->is_published)
                                    <br><small class="text-muted" style="font-size:.65rem;">
                                        <i class="fas fa-bell me-1"></i>Terkirim
                                    </small>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center flex-wrap">
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
                                          onsubmit="return confirm('Publikasikan jadwal ini?\nNotifikasi akan dikirim ke semua guru dan siswa terkait.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success"
                                                title="Publikasikan & Kirim Notifikasi">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST"
                                          action="{{ route('admin.exam-schedules.destroy', $schedule) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus jadwal \'{{ addslashes($schedule->title) }}\'?')">
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
                                <p class="text-muted small">Buat jadwal ujian praktikum baru untuk dikirimkan ke guru dan siswa.</p>
                                <a href="{{ route('admin.exam-schedules.create') }}"
                                   class="btn btn-primary btn-sm mt-1">
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
        <div class="card-footer bg-white border-top py-3">
            {{ $schedules->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
