@extends('layouts.admin')

@section('title', 'Detail Kelas — ' . $kelas->name)
@section('page-title', 'Detail Kelas')
@section('page-subtitle', $kelas->name . ' · ' . ($kelas->grade ?? '') . ' · ' . ($kelas->jurusan?->name ?? ''))

@section('page-actions')
    <a href="{{ route('admin.kelas.edit', $kelas->id) }}" class="btn btn-warning btn-sm me-1">
        <i class="fas fa-edit me-1"></i>Edit
    </a>
    <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

<div class="row g-4">

    {{-- ═══ KIRI: Info Kelas ═══ --}}
    <div class="col-lg-4">

        {{-- Info card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-school me-2 text-primary"></i>Informasi Kelas
                </h6>
            </div>
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center
                            justify-content-center mb-3"
                     style="width:72px;height:72px;">
                    <i class="fas fa-school text-primary fa-2x"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $kelas->name }}</h5>
                @if($kelas->grade)
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-2">Kelas {{ $kelas->grade }}</span>
                @endif
                <p class="text-muted small mb-0">{{ $kelas->jurusan?->name ?? 'Jurusan belum ditentukan' }}</p>
            </div>
            <div class="card-body pt-0">
                <table class="table table-sm small mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted ps-0" style="width:40%">Tingkat</td>
                            <td class="fw-semibold">{{ $kelas->grade ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Jurusan</td>
                            <td>
                                @php
                                    $jName  = $kelas->jurusan?->name ?? null;
                                    $jColors = ['primary','success','info','warning','danger'];
                                    $jc     = $jName ? $jColors[abs(crc32($jName)) % count($jColors)] : 'secondary';
                                @endphp
                                @if($jName)
                                    <span class="badge bg-{{ $jc }}">{{ $jName }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Tahun Ajaran</td>
                            <td class="fw-semibold">{{ $kelas->academic_year ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Status</td>
                            <td>
                                @if(($kelas->status ?? 'active') === 'active')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Jumlah Siswa</td>
                            <td class="fw-semibold">{{ $kelas->siswa->count() }} siswa</td>
                        </tr>

                        @if($kelas->created_at)
                        <tr>
                            <td class="text-muted ps-0 border-0">Dibuat</td>
                            <td class="border-0">{{ \Carbon\Carbon::parse($kelas->created_at)->format('d M Y') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top d-flex gap-2 py-3">
                <a href="{{ route('admin.kelas.edit', $kelas->id) }}" class="btn btn-warning btn-sm flex-fill">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm flex-fill"
                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash me-1"></i>Hapus
                </button>
            </div>
        </div>

        {{-- Stats mini --}}
        <div class="row g-3">
            @php $totalSiswa = $kelas->siswa->count(); @endphp
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="h3 fw-bold text-primary mb-0">{{ $totalSiswa }}</div>
                    <small class="text-muted">Total Siswa</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="h3 fw-bold text-success mb-0">
                        {{-- is_active ada di users_central (via relasi user), bukan di tabel siswa --}}
                        {{ $kelas->siswa->filter(fn($s) => $s->user?->is_active)->count() }}
                    </div>
                    <small class="text-muted">Aktif</small>
                </div>
            </div>
        </div>

    </div>{{-- /col-lg-4 --}}

    {{-- ═══ KANAN: Daftar Siswa ═══ --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-users me-2 text-success"></i>Daftar Siswa
                </h6>
                <span class="badge bg-secondary">{{ $kelas->siswa->count() }} siswa</span>
            </div>

            @if($kelas->siswa->count() > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Nama Siswa</th>
                                <th>NIS</th>
                                <th>Email</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelas->siswa as $i => $siswa)
                            @php $user = $siswa->user; @endphp
                            <tr>
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @php
                                            $sName = $user?->name ?? 'S';
                                            $sBg   = ['#0891b2','#7c3aed','#16a34a','#d97706','#dc2626'][abs(crc32($sName)) % 5];
                                        @endphp
                                        @if($user?->photo)
                                            <img src="{{ $user->photo_url }}"
                                                 class="rounded-circle flex-shrink-0"
                                                 style="width:34px;height:34px;object-fit:cover;" alt=""
                                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                            <div class="rounded-circle flex-shrink-0 d-none align-items-center
                                                        justify-content-center text-white fw-bold"
                                                 style="width:34px;height:34px;background:{{ $sBg }};font-size:.8rem;">
                                                {{ strtoupper(substr($sName,0,1)) }}
                                            </div>
                                        @else
                                            <div class="rounded-circle flex-shrink-0 d-flex align-items-center
                                                        justify-content-center text-white fw-bold"
                                                 style="width:34px;height:34px;background:{{ $sBg }};font-size:.8rem;">
                                                {{ strtoupper(substr($sName,0,1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $user?->name ?? '—' }}</div>
                                            <small class="text-muted">{{ $user?->phone ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($siswa->nis)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            {{ $siswa->nis }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $user?->email ?? '—' }}</td>
                                <td class="text-center">
                                    @if($user?->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if($user)
                                        <a href="{{ route('admin.users.show', $user->id) }}"
                                           class="btn btn-outline-info btn-sm" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                           class="btn btn-outline-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body text-center py-5">
                <i class="fas fa-user-slash fa-3x text-muted opacity-25 mb-3 d-block"></i>
                <h6 class="text-muted">Belum ada siswa di kelas ini</h6>
                <p class="text-muted small">Siswa dapat ditambahkan dari menu Manajemen Siswa.</p>
                <a href="{{ route('admin.users.create.siswa') }}" class="btn btn-primary btn-sm mt-2">
                    <i class="fas fa-user-plus me-1"></i>Tambah Siswa Baru
                </a>
            </div>
            @endif
        </div>
    </div>{{-- /col-lg-8 --}}

</div>{{-- /row --}}

{{-- ── Mata Pelajaran per Jurusan ── --}}
@php
    $groupColors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
@endphp

<div class="row g-3 mt-1 mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0">
                <i class="fas fa-book-open me-2 text-primary"></i>
                Mata Pelajaran Kelas Ini
                <span class="badge bg-primary bg-opacity-10 text-primary ms-1">
                    {{ $kelas->subjects->count() }} mapel
                </span>
            </h6>
            <a href="{{ route('admin.kelas.edit', $kelas->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-edit me-1"></i>Kelola Mapel
            </a>
        </div>
    </div>

    @if($kelas->subjects->isEmpty())
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-book fa-3x text-muted opacity-25 mb-3 d-block"></i>
                <h6 class="text-muted">Belum ada mata pelajaran</h6>
                <p class="text-muted small mb-3">Tambahkan mata pelajaran melalui halaman Edit Kelas.</p>
                <a href="{{ route('admin.kelas.edit', $kelas->id) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Tambah Mata Pelajaran
                </a>
            </div>
        </div>
    </div>
    @else
        @foreach($subjectsByJurusan as $gJurusanId => $subjects)
            @php
                $keys      = array_values($subjectsByJurusan->keys()->toArray());
                $colorIdx  = array_search($gJurusanId, $keys);
                $color     = $groupColors[$colorIdx % count($groupColors)];
                $jur       = $gJurusanId ? $jurusanMap->get($gJurusanId) : null;
                $jurName   = $jur?->name ?? 'Umum / Tanpa Jurusan';
                $jurCode   = $jur?->code ?? null;
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header py-2 bg-{{ $color }} bg-opacity-10 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-graduation-cap text-{{ $color }}"></i>
                            <div class="fw-semibold small text-{{ $color }}">
                                {{ $jurName }}
                                @if($jurCode)
                                    <span class="fw-normal opacity-75">({{ $jurCode }})</span>
                                @endif
                            </div>
                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} ms-auto"
                                  style="font-size:.65rem;">
                                {{ $subjects->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($subjects->sortBy('name') as $subj)
                            <li class="list-group-item px-3 py-2 small d-flex align-items-center gap-2">
                                <i class="fas fa-book-open text-{{ $color }} fa-xs flex-shrink-0"></i>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $subj->name }}</div>
                                    <div class="d-flex gap-1 mt-1 flex-wrap">
                                        @if($subj->code)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary"
                                              style="font-size:.6rem;">{{ $subj->code }}</span>
                                        @endif
                                        @php
                                            $tc = match($subj->type) {
                                                'teori'     => ['info',    'Teori'],
                                                'praktikum' => ['warning', 'Praktikum'],
                                                default     => ['primary', 'Campuran'],
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $tc[0] }} bg-opacity-10 text-{{ $tc[0] }}"
                                              style="font-size:.6rem;">{{ $tc[1] }}</span>
                                        @if($subj->sks)
                                        <span class="badge bg-light text-muted"
                                              style="font-size:.6rem;">{{ $subj->sks }} SKS</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="text-muted mb-2">Hapus kelas <strong>{{ $kelas->name }}</strong>?</p>

                @if($kelas->siswa->count() > 0)
                    <div class="alert alert-warning text-start small">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Kelas ini masih memiliki <strong>{{ $kelas->siswa->count() }} siswa</strong>.
                        Pindahkan semua siswa sebelum menghapus.
                    </div>
                @else
                    <div class="alert alert-info text-start small">
                        <i class="fas fa-info-circle me-1"></i>
                        Kelas kosong dan aman untuk dihapus.
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                @if($kelas->siswa->count() === 0)
                    <form action="{{ route('admin.kelas.destroy', $kelas->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Ya, Hapus
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-secondary" disabled>
                        <i class="fas fa-ban me-1"></i>Tidak Bisa Dihapus
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
