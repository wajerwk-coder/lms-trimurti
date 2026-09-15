@php
    $userRole = auth()->user()->role ?? 'siswa';
    $layout   = match($userRole) {
        'admin' => 'layouts.admin',
        'guru'  => 'layouts.guru',
        default => 'layouts.siswa',
    };
    $indexRoute = 'notifications.index';

    $tipe    = $notification->tipe ?? $notification->type ?? 'info';
    $judul   = $notification->judul ?? $notification->title ?? 'Notifikasi';
    $pesan   = $notification->pesan ?? $notification->message ?? '';
    $urlAksi = $notification->url_aksi ?? $notification->action_url ?? null;
    $data    = $notification->data ? (is_string($notification->data) ? json_decode($notification->data, true) : $notification->data) : [];

    $colorMap = [
        'peringatan'    => 'warning',
        'sukses'        => 'success',
        'error'         => 'danger',
        'sistem'        => 'secondary',
        'pengumuman'    => 'info',
        'exam'          => 'primary',
        'exam_schedule' => 'primary',
        'ujian'         => 'primary',
        'assignment'    => 'warning',
        'tugas'         => 'warning',
    ];
    $iconMap = [
        'peringatan'    => 'fa-exclamation-triangle',
        'sukses'        => 'fa-check-circle',
        'error'         => 'fa-times-circle',
        'sistem'        => 'fa-cog',
        'pengumuman'    => 'fa-bullhorn',
        'exam'          => 'fa-calendar-check',
        'exam_schedule' => 'fa-calendar-check',
        'ujian'         => 'fa-calendar-check',
        'assignment'    => 'fa-tasks',
        'tugas'         => 'fa-tasks',
    ];
    $color = $colorMap[$tipe] ?? 'primary';
    $icon  = $iconMap[$tipe] ?? 'fa-bell';

    $tipePenerima = $notification->tipe_penerima ?? 'user';
    $tujuanLabel  = match($tipePenerima) {
        'semua', 'all' => 'Semua Pengguna',
        'guru'         => 'Semua Guru',
        'siswa'        => 'Semua Siswa',
        default        => 'Khusus Anda',
    };
@endphp

@extends($layout)

@section('title', 'Detail Notifikasi')
@section('page-title', 'Detail Notifikasi')
@section('page-subtitle', $judul)

@section('page-actions')
    <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali ke Notifikasi
    </a>
@endsection

@section('content')

<div class="row g-4">

    {{-- ── Konten Utama ── --}}
    <div class="col-lg-8">

        {{-- Header notifikasi --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="p-4" style="background:linear-gradient(135deg,
                    {{ $color === 'warning' ? '#d97706,#b45309' :
                      ($color === 'danger'  ? '#dc2626,#b91c1c' :
                      ($color === 'success' ? '#16a34a,#15803d' :
                      ($color === 'info'    ? '#0891b2,#0e7490' :
                                             '#4f46e5,#3730a3'))) }});">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-white bg-opacity-20 d-flex align-items-center
                                    justify-content-center flex-shrink-0"
                             style="width:56px;height:56px;">
                            <i class="fas {{ $icon }} text-white fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold text-white mb-1">{{ $judul }}</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge rounded-pill bg-white bg-opacity-20 text-white"
                                      style="font-size:.75rem;">
                                    <i class="fas fa-tag me-1"></i>{{ ucfirst($tipe) }}
                                </span>
                                <span class="badge rounded-pill bg-white bg-opacity-20 text-white"
                                      style="font-size:.75rem;">
                                    <i class="fas fa-users me-1"></i>{{ $tujuanLabel }}
                                </span>
                                <span class="badge rounded-pill bg-success bg-opacity-75 text-white"
                                      style="font-size:.75rem;">
                                    <i class="fas fa-check-circle me-1"></i>Sudah Dibaca
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Isi pesan --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-envelope-open me-2 text-primary"></i>Isi Notifikasi
                </h6>
            </div>
            <div class="card-body">
                @if($pesan)
                    <div class="p-3 bg-light rounded-3" style="white-space:pre-line;line-height:1.8;font-size:.95rem;">
                        {{ $pesan }}
                    </div>
                @else
                    <p class="text-muted mb-0"><em>Tidak ada isi pesan.</em></p>
                @endif

                @if($urlAksi)
                    <div class="mt-3">
                        <a href="{{ $urlAksi }}" class="btn btn-{{ $color }} btn-sm px-4">
                            <i class="fas fa-external-link-alt me-2"></i>Lihat Detail Lengkap
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Data tambahan (JSON) --}}
        @if(!empty($data))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-info-circle me-2 text-info"></i>Informasi Tambahan
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <tbody>
                            @foreach($data as $key => $value)
                            @if(!is_array($value) && !is_null($value))
                            <tr>
                                <td class="ps-3 fw-semibold text-muted" style="width:35%;">
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                </td>
                                <td class="text-dark">{{ $value }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Sidebar Info ── --}}
    <div class="col-lg-4">

        {{-- Meta notifikasi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-info me-2 text-primary"></i>Informasi Notifikasi
                </h6>
            </div>
            <div class="card-body small">
                <div class="d-flex flex-column gap-3">

                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-clock text-muted mt-1 flex-shrink-0" style="width:16px;"></i>
                        <div>
                            <div class="text-muted" style="font-size:.7rem;">DIKIRIM</div>
                            <div class="fw-semibold">
                                {{ $notification->created_at ? $notification->created_at->format('d M Y, H:i') : '—' }}
                            </div>
                            <div class="text-muted">
                                {{ $notification->created_at ? $notification->created_at->diffForHumans() : '' }}
                            </div>
                        </div>
                    </div>

                    @if($notification->read_at)
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-envelope-open text-success mt-1 flex-shrink-0" style="width:16px;"></i>
                        <div>
                            <div class="text-muted" style="font-size:.7rem;">DIBACA</div>
                            <div class="fw-semibold text-success">
                                {{ $notification->read_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($notification->sender)
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-user-shield text-muted mt-1 flex-shrink-0" style="width:16px;"></i>
                        <div>
                            <div class="text-muted" style="font-size:.7rem;">PENGIRIM</div>
                            <div class="fw-semibold">{{ $notification->sender->name }}</div>
                            <div class="text-muted">{{ ucfirst($notification->sender->role ?? '') }}</div>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-users text-muted mt-1 flex-shrink-0" style="width:16px;"></i>
                        <div>
                            <div class="text-muted" style="font-size:.7rem;">DITUJUKAN UNTUK</div>
                            <div class="fw-semibold">{{ $tujuanLabel }}</div>
                        </div>
                    </div>

                    @if($notification->prioritas)
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-flag text-muted mt-1 flex-shrink-0" style="width:16px;"></i>
                        <div>
                            <div class="text-muted" style="font-size:.7rem;">PRIORITAS</div>
                            @php
                                $prioColor = match($notification->prioritas) {
                                    'tinggi', 'darurat' => 'danger',
                                    'sedang' => 'warning',
                                    default  => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $prioColor }}">
                                {{ ucfirst($notification->prioritas) }}
                            </span>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- Aksi --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column gap-2">
                @if($urlAksi)
                    <a href="{{ $urlAksi }}" class="btn btn-{{ $color }} fw-semibold">
                        <i class="fas fa-external-link-alt me-2"></i>Lihat Detail Terkait
                    </a>
                @endif
                <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                </a>
                <hr class="my-1">
                <button type="button" class="btn btn-outline-danger btn-sm"
                        onclick="hapusNotifikasi({{ $notification->id }})">
                    <i class="fas fa-trash me-1"></i>Hapus Notifikasi Ini
                </button>
            </div>
        </div>

    </div>

</div>

@push('js')
<script>
function hapusNotifikasi(id) {
    if (!confirm('Hapus notifikasi ini?')) return;
    fetch(`/notifications/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("notifications.index") }}';
        }
    });
}
</script>
@endpush

@endsection
