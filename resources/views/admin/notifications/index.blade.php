@extends('layouts.admin')

@section('title', 'Manajemen Notifikasi')
@section('page-title', 'Manajemen Notifikasi')
@section('page-subtitle', 'Kirim dan kelola notifikasi ke guru & siswa')

@section('content')
<div class="container-fluid px-0">

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="fas fa-bell text-primary me-2"></i>Manajemen Notifikasi
                    </h5>
                    <p class="text-muted mb-0 small">Kelola pengiriman notifikasi ke seluruh guru dan siswa</p>
                </div>
                <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Notifikasi Baru
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-primary">{{ $stats['total'] }}</div>
                    <div class="small text-muted">Total Terkirim</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-success">{{ $stats['semua'] }}</div>
                    <div class="small text-muted">Ke Semua</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-info">{{ $stats['guru'] }}</div>
                    <div class="small text-muted">Ke Guru</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-warning">{{ $stats['siswa'] }}</div>
                    <div class="small text-muted">Ke Siswa</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-2 align-items-end">
                <div class="col-sm-4 col-md-3">
                    <label class="form-label small mb-1">Tujuan Notifikasi</label>
                    <select name="tipe_penerima" class="form-select form-select-sm">
                        <option value="">Semua Tujuan</option>
                        <option value="semua"  {{ request('tipe_penerima') === 'semua'  ? 'selected' : '' }}>Semua Pengguna</option>
                        <option value="guru"   {{ request('tipe_penerima') === 'guru'   ? 'selected' : '' }}>Guru</option>
                        <option value="siswa"  {{ request('tipe_penerima') === 'siswa'  ? 'selected' : '' }}>Siswa</option>
                        <option value="user"   {{ request('tipe_penerima') === 'user'   ? 'selected' : '' }}>User Spesifik</option>
                    </select>
                </div>
                <div class="col-sm-4 col-md-3">
                    <label class="form-label small mb-1">Tipe Notifikasi</label>
                    <select name="tipe" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        <option value="info"         {{ request('tipe') === 'info'         ? 'selected' : '' }}>Info</option>
                        <option value="pengumuman"   {{ request('tipe') === 'pengumuman'   ? 'selected' : '' }}>Pengumuman</option>
                        <option value="peringatan"   {{ request('tipe') === 'peringatan'   ? 'selected' : '' }}>Peringatan</option>
                        <option value="sukses"       {{ request('tipe') === 'sukses'       ? 'selected' : '' }}>Sukses</option>
                        <option value="sistem"       {{ request('tipe') === 'sistem'       ? 'selected' : '' }}>Sistem</option>
                    </select>
                </div>
                <div class="col-sm-4 col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Notifikasi --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($notifications->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Notifikasi</th>
                            <th>Tujuan</th>
                            <th>Tipe</th>
                            <th>Prioritas</th>
                            <th>Waktu</th>
                            <th width="80" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notif)
                        @php
                            $tipeColor = match($notif->tipe) {
                                'peringatan'  => 'warning',
                                'sukses'      => 'success',
                                'error'       => 'danger',
                                'sistem'      => 'secondary',
                                'pengumuman'  => 'info',
                                default       => 'primary',
                            };
                            $tipeIcon = match($notif->tipe) {
                                'peringatan'  => 'fa-exclamation-triangle',
                                'sukses'      => 'fa-check-circle',
                                'error'       => 'fa-times-circle',
                                'sistem'      => 'fa-cog',
                                'pengumuman'  => 'fa-bullhorn',
                                default       => 'fa-info-circle',
                            };
                            $penerimaLabel = match($notif->tipe_penerima) {
                                'semua'  => ['label' => 'Semua Pengguna', 'badge' => 'success',   'icon' => 'fa-users'],
                                'guru'   => ['label' => 'Semua Guru',     'badge' => 'info',      'icon' => 'fa-chalkboard-teacher'],
                                'siswa'  => ['label' => 'Semua Siswa',    'badge' => 'warning',   'icon' => 'fa-user-graduate'],
                                'user'   => ['label' => 'User Spesifik',  'badge' => 'secondary', 'icon' => 'fa-user'],
                                default  => ['label' => ucfirst($notif->tipe_penerima ?? '-'), 'badge' => 'light', 'icon' => 'fa-user'],
                            };
                            $prioritasColor = match($notif->prioritas) {
                                'tinggi'  => 'warning',
                                'darurat' => 'danger',
                                'rendah'  => 'secondary',
                                default   => 'success',
                            };
                        @endphp
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="bg-{{ $tipeColor }} bg-opacity-10 text-{{ $tipeColor }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:38px;height:38px;">
                                        <i class="fas {{ $tipeIcon }} fa-sm"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $notif->judul ?? $notif->title ?? '-' }}</div>
                                        <div class="text-muted small" style="max-width:320px;">
                                            {{ Str::limit($notif->pesan ?? $notif->message ?? '', 80) }}
                                        </div>
                                        @if($notif->url_aksi)
                                            <a href="{{ $notif->url_aksi }}" target="_blank" class="small text-primary">
                                                <i class="fas fa-link me-1"></i>Tautan terlampir
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $penerimaLabel['badge'] }} bg-opacity-15 text-{{ $penerimaLabel['badge'] }} border border-{{ $penerimaLabel['badge'] }} border-opacity-25 px-2 py-1">
                                    <i class="fas {{ $penerimaLabel['icon'] }} me-1"></i>
                                    {{ $penerimaLabel['label'] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $tipeColor }} bg-opacity-15 text-{{ $tipeColor }}">
                                    {{ ucfirst($notif->tipe ?? '-') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $prioritasColor }} bg-opacity-15 text-{{ $prioritasColor }}">
                                    {{ ucfirst($notif->prioritas ?? 'sedang') }}
                                </span>
                            </td>
                            <td>
                                <div class="small text-muted">
                                    {{ $notif->created_at ? \Carbon\Carbon::parse($notif->created_at)->diffForHumans() : '-' }}
                                </div>
                                <div class="small text-muted">
                                    {{ $notif->created_at ? \Carbon\Carbon::parse($notif->created_at)->format('d/m/Y H:i') : '' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.notifications.destroy', $notif->id) }}"
                                      onsubmit="return confirm('Hapus notifikasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($notifications->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $notifications->links() }}
            </div>
            @endif

            @else
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">Belum ada notifikasi terkirim</h6>
                <p class="text-muted small">Klik tombol "Kirim Notifikasi Baru" untuk mulai mengirim pesan ke guru atau siswa.</p>
                <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary mt-2">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Notifikasi Pertama
                </a>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
