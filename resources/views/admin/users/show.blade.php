@extends('layouts.admin')

@section('title', 'Detail Pengguna — ' . $user->name)
@section('page-title', 'Detail Pengguna')
@section('page-subtitle', ucfirst($user->role) . ': ' . $user->name)

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        @php
            $backRoute = match($user->role) {
                'guru'  => route('admin.users.guru'),
                'siswa' => route('admin.users.siswa'),
                default => route('admin.users.index'),
            };
        @endphp
        <a href="{{ $backRoute }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
@endsection

@push('css')
<style>
.profile-hero {
    background: linear-gradient(135deg,
        {{ ['admin'=>'#ef4444,#dc2626','guru'=>'#16a34a,#059669','siswa'=>'#d97706,#b45309'][$user->role] ?? '#64748b,#475569' }}
    );
    border-radius: 16px;
    overflow: hidden;
    position: relative;
}
.profile-hero::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:180px; height:180px;
    background:rgba(255,255,255,.08); border-radius:50%;
}
.info-row {
    display: flex; align-items: flex-start; gap: .6rem;
    padding: .5rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.info-row:last-child { border-bottom: none; }
.info-icon {
    width: 28px; height: 28px;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .7rem;
}
.stat-mini {
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    transition: transform .15s;
}
.stat-mini:hover { transform: translateY(-2px); }
</style>
@endpush

@section('content')

@php
    $roleColor = ['admin'=>'danger','guru'=>'success','siswa'=>'warning'][$user->role] ?? 'secondary';
    $roleIcon  = ['admin'=>'fa-user-shield','guru'=>'fa-chalkboard-teacher','siswa'=>'fa-user-graduate'][$user->role] ?? 'fa-user';
    $guruProfile  = $user->role === 'guru'  ? $user->guruProfile  : null;
    $siswaProfile = $user->role === 'siswa' ? $user->siswaProfile : null;
@endphp

<div class="row g-4">

    {{-- ── Kolom Kiri: Profile Card ──────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Hero Card --}}
        <div class="profile-hero p-4 mb-4">
            <div class="text-center text-white mb-3">
                @if($user->photo)
                    <img src="{{ asset('storage/'.$user->photo) }}"
                         class="rounded-circle border border-3 border-white border-opacity-50 mb-3"
                         style="width:90px;height:90px;object-fit:cover;">
                @else
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center
                                fw-bold text-white mb-3 border border-3 border-white border-opacity-25"
                         style="width:90px;height:90px;font-size:2.2rem;background:rgba(255,255,255,.2);">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold mb-1 lh-sm">{{ $user->name }}</h5>
                <div class="opacity-75 small mb-2">{{ $user->email }}</div>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <span class="badge bg-white bg-opacity-25 text-white">
                        <i class="fas {{ $roleIcon }} me-1"></i>{{ ucfirst($user->role) }}
                    </span>
                    @if($user->is_active)
                        <span class="badge bg-white bg-opacity-25 text-white">
                            <i class="fas fa-check-circle me-1"></i>Aktif
                        </span>
                    @else
                        <span class="badge bg-dark bg-opacity-50 text-white">
                            <i class="fas fa-times-circle me-1"></i>Nonaktif
                        </span>
                    @endif
                </div>
            </div>

            {{-- Mini Stats --}}
            <div class="row g-2 mt-1">
                @if(!empty($stats))
                    @foreach(array_slice($stats, 0, 4, true) as $label => $val)
                    <div class="col-6">
                        <div class="text-center py-2 rounded-2 bg-white bg-opacity-15">
                            <div class="fw-bold text-white fs-5 lh-1">{{ $val }}</div>
                            <div class="opacity-75 text-white" style="font-size:.68rem;">{{ $label }}</div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="col-6">
                        <div class="text-center py-2 rounded-2 bg-white bg-opacity-15">
                            <div class="fw-bold text-white fs-5 lh-1">{{ $user->created_at->format('Y') }}</div>
                            <div class="opacity-75 text-white" style="font-size:.68rem;">Tahun Bergabung</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center py-2 rounded-2 bg-white bg-opacity-15">
                            <div class="fw-bold text-white fs-5 lh-1">{{ $user->created_at->diffInMonths(now()) }}</div>
                            <div class="opacity-75 text-white" style="font-size:.68rem;">Bulan Aktif</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Aksi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-bolt me-2 text-warning"></i>Aksi</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit me-2"></i>Edit Profil
                </a>
                @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                          onsubmit="return confirm('Hapus pengguna {{ addslashes($user->name) }}? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-trash me-2"></i>Hapus Akun
                        </button>
                    </form>
                @else
                    <div class="alert alert-info mb-0 py-2 small">
                        <i class="fas fa-info-circle me-1"></i>Ini adalah akun Anda.
                    </div>
                @endif
                <a href="{{ $backRoute }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                </a>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-clock me-2 text-secondary"></i>Timeline</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3 small">
                    <div class="d-flex gap-2 align-items-start">
                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:28px;height:28px;">
                            <i class="fas fa-user-plus text-success" style="font-size:.65rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Akun dibuat</div>
                            <div class="text-muted">{{ $user->created_at->format('d M Y, H:i') }}</div>
                            <div class="text-muted" style="font-size:.7rem;">{{ $user->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @if($user->updated_at && $user->updated_at->ne($user->created_at))
                    <div class="d-flex gap-2 align-items-start">
                        <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:28px;height:28px;">
                            <i class="fas fa-edit text-warning" style="font-size:.65rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Terakhir diupdate</div>
                            <div class="text-muted">{{ $user->updated_at->format('d M Y, H:i') }}</div>
                            <div class="text-muted" style="font-size:.7rem;">{{ $user->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Kolom Kanan: Detail ─────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Info Akun --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-id-card me-2 text-primary"></i>Informasi Akun
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-0">
                    @foreach([
                        ['fa-user',      'primary',   'Nama Lengkap', $user->name],
                        ['fa-envelope',  'info',      'Email',        $user->email],
                        ['fa-at',        'secondary', 'Username',     $user->username ?? '—'],
                        ['fa-phone',     'success',   'Telepon',      $user->phone ?? '—'],
                        ['fa-shield-alt','danger',    'Role',         ucfirst($user->role)],
                        ['fa-circle',    'success',   'Status',       $user->is_active ? 'Aktif' : 'Nonaktif'],
                    ] as [$ic, $col, $label, $val])
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-icon bg-{{ $col }} bg-opacity-10">
                                <i class="fas {{ $ic }} text-{{ $col }}"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.7rem;">{{ $label }}</div>
                                <div class="fw-semibold text-dark" style="font-size:.85rem;">{{ $val }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Profil Guru --}}
        @if($guruProfile)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chalkboard-teacher me-2 text-success"></i>Profil Guru
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-0">
                    @foreach([
                        ['fa-id-badge',      'info',      'NIP',               $guruProfile->nip ?? '—'],
                        ['fa-book',          'primary',   'Mata Pelajaran',     $guruProfile->mata_pelajaran ?? '—'],
                        ['fa-graduation-cap','warning',   'Pendidikan',         $guruProfile->pendidikan_terakhir ?? '—'],
                        ['fa-university',    'secondary', 'Jurusan Pend.',      $guruProfile->jurusan_pendidikan ?? '—'],
                        ['fa-map-marker',    'danger',    'Tempat Lahir',       $guruProfile->tempat_lahir ?? '—'],
                        ['fa-birthday-cake', 'success',   'Tanggal Lahir',      $guruProfile->tanggal_lahir?->format('d M Y') ?? '—'],
                        ['fa-venus-mars',    'info',      'Jenis Kelamin',      $guruProfile->jenis_kelamin == 'L' ? 'Laki-laki' : ($guruProfile->jenis_kelamin == 'P' ? 'Perempuan' : '—')],
                        ['fa-calendar',      'secondary', 'Mulai Kerja',        $guruProfile->tahun_mulai_kerja ?? '—'],
                    ] as [$ic, $col, $label, $val])
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-icon bg-{{ $col }} bg-opacity-10">
                                <i class="fas {{ $ic }} text-{{ $col }}"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.7rem;">{{ $label }}</div>
                                <div class="fw-semibold text-dark" style="font-size:.85rem;">{{ $val }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($guruProfile->address ?? $guruProfile->alamat ?? null)
                    <div class="col-12">
                        <div class="info-row">
                            <div class="info-icon bg-secondary bg-opacity-10">
                                <i class="fas fa-home text-secondary"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.7rem;">Alamat</div>
                                <div class="fw-semibold text-dark" style="font-size:.85rem;">
                                    {{ $guruProfile->address ?? $guruProfile->alamat ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Profil Siswa --}}
        @if($siswaProfile)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-user-graduate me-2 text-warning"></i>Profil Siswa
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-0">
                    @foreach([
                        ['fa-id-badge',      'primary',   'NIS',             $siswaProfile->nis ?? '—'],
                        ['fa-id-card',       'secondary', 'NISN',            $siswaProfile->nisn ?? '—'],
                        ['fa-door-open',     'success',   'Kelas',           $siswaProfile->kelas?->name ?? '—'],
                        ['fa-graduation-cap','warning',   'Jurusan',         $siswaProfile->major ?? '—'],
                        ['fa-calendar-alt',  'info',      'Tahun Ajaran',    $siswaProfile->tahun_ajaran ?? '—'],
                        ['fa-map-marker',    'danger',    'Tempat Lahir',    $siswaProfile->tempat_lahir ?? '—'],
                        ['fa-birthday-cake', 'success',   'Tanggal Lahir',   $siswaProfile->tanggal_lahir?->format('d M Y') ?? '—'],
                        ['fa-venus-mars',    'info',      'Jenis Kelamin',   $siswaProfile->jenis_kelamin == 'L' ? 'Laki-laki' : ($siswaProfile->jenis_kelamin == 'P' ? 'Perempuan' : '—')],
                        ['fa-users',         'secondary', 'Orang Tua / Wali',$siswaProfile->nama_ortu ?? '—'],
                        ['fa-phone',         'primary',   'Telepon Ortu',    $siswaProfile->no_telepon_ortu ?? '—'],
                        ['fa-tint',          'danger',    'Golongan Darah',  $siswaProfile->golongan_darah ?? '—'],
                        ['fa-heartbeat',     'warning',   'Riwayat Penyakit',$siswaProfile->riwayat_penyakit ?? '—'],
                    ] as [$ic, $col, $label, $val])
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-icon bg-{{ $col }} bg-opacity-10">
                                <i class="fas {{ $ic }} text-{{ $col }}"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.7rem;">{{ $label }}</div>
                                <div class="fw-semibold text-dark" style="font-size:.85rem;">{{ $val }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($siswaProfile->alamat)
                    <div class="col-12">
                        <div class="info-row">
                            <div class="info-icon bg-secondary bg-opacity-10">
                                <i class="fas fa-home text-secondary"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.7rem;">Alamat</div>
                                <div class="fw-semibold text-dark" style="font-size:.85rem;">{{ $siswaProfile->alamat }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Statistik Tambahan --}}
        @if(!empty($stats))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-bar me-2 text-info"></i>Statistik Aktivitas
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($stats as $label => $val)
                    <div class="col-6 col-md-3">
                        <div class="stat-mini text-center p-3 h-100">
                            <div class="h3 fw-bold mb-0 text-primary">{{ $val }}</div>
                            <div class="small text-muted mt-1">{{ str_replace('_', ' ', $label) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Aktivitas Terbaru --}}
        @if($activities->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-history me-2 text-secondary"></i>Aktivitas Terbaru
                </h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($activities->take(8) as $act)
                    <li class="list-group-item px-4 py-2 small">
                        <div class="d-flex gap-2">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center
                                        justify-content-center flex-shrink-0"
                                 style="width:26px;height:26px;margin-top:2px;">
                                <i class="fas fa-bell text-primary" style="font-size:.6rem;"></i>
                            </div>
                            <div>
                                <div class="text-dark">{{ $act->description ?? $act }}</div>
                                <div class="text-muted" style="font-size:.7rem;">
                                    {{ optional($act->created_at)->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

    </div>{{-- /col-lg-8 --}}
</div>{{-- /row --}}

@endsection