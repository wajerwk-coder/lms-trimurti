@php
    $items = $notifications instanceof \Illuminate\Pagination\LengthAwarePaginator
        ? $notifications->getCollection()
        : collect($notifications);
@endphp

@if($items->count() > 0)
<div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
            <tr>
                <th width="40" class="px-3">Status</th>
                <th>Notifikasi</th>
                <th width="160">Tujuan / Pengirim</th>
                <th width="130">Waktu</th>
                <th width="90" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $notification)
            @php
                $isRead   = !is_null($notification->read_at);
                $tipe     = $notification->tipe ?? $notification->type ?? 'info';
                $judul    = $notification->judul ?? $notification->title ?? 'Notifikasi';
                $pesan    = $notification->pesan ?? $notification->message ?? '';
                $urlAksi  = $notification->url_aksi ?? $notification->action_url ?? null;
                $createdAt = $notification->created_at
                    ? \Carbon\Carbon::parse($notification->created_at)
                    : null;

                // Warna & ikon berdasarkan tipe
                $colorMap = [
                    'peringatan'   => 'warning',
                    'sukses'       => 'success',
                    'error'        => 'danger',
                    'sistem'       => 'secondary',
                    'pengumuman'   => 'info',
                    'exam'         => 'purple',
                    'exam_schedule'=> 'purple',
                    'ujian'        => 'purple',
                    'assignment'   => 'orange',
                    'tugas'        => 'orange',
                ];
                $iconMap = [
                    'peringatan'   => 'fa-exclamation-triangle',
                    'sukses'       => 'fa-check-circle',
                    'error'        => 'fa-times-circle',
                    'sistem'       => 'fa-cog',
                    'pengumuman'   => 'fa-bullhorn',
                    'exam'         => 'fa-calendar-check',
                    'exam_schedule'=> 'fa-calendar-check',
                    'ujian'        => 'fa-calendar-check',
                    'assignment'   => 'fa-tasks',
                    'tugas'        => 'fa-tasks',
                ];
                $color = $colorMap[$tipe] ?? 'primary';
                $icon  = $iconMap[$tipe] ?? 'fa-bell';

                // Label tujuan untuk menjelaskan notifikasi ini ditujukan ke siapa
                $tipePenerima = $notification->tipe_penerima ?? $notification->receiver_type ?? null;
                $tujuanMap = [
                    'semua' => ['label' => 'Semua Pengguna', 'badge' => 'success',   'icon' => 'fa-users'],
                    'guru'  => ['label' => 'Semua Guru',     'badge' => 'info',      'icon' => 'fa-chalkboard-teacher'],
                    'siswa' => ['label' => 'Semua Siswa',    'badge' => 'warning',   'icon' => 'fa-user-graduate'],
                    'user'  => ['label' => 'Khusus Anda',    'badge' => 'primary',   'icon' => 'fa-user'],
                    'all'   => ['label' => 'Semua Pengguna', 'badge' => 'success',   'icon' => 'fa-users'],
                ];
                $tujuan = $tujuanMap[$tipePenerima] ?? ['label' => 'Notifikasi Sistem', 'badge' => 'secondary', 'icon' => 'fa-bell'];

                // Bootstrap tidak punya 'purple' & 'orange' — fallback ke warna terdekat
                $bsColor = match($color) {
                    'purple' => 'primary',
                    'orange' => 'warning',
                    default  => $color,
                };
            @endphp
            <tr id="notification-{{ $notification->id }}"
                class="{{ $isRead ? '' : 'table-warning bg-opacity-25' }}">

                {{-- Status --}}
                <td class="px-3 text-center">
                    @if($isRead)
                        <i class="fas fa-envelope-open text-muted" title="Sudah dibaca"></i>
                    @else
                        <i class="fas fa-envelope text-primary" title="Belum dibaca"></i>
                        <span class="badge bg-danger rounded-pill d-block mt-1 unread-badge" style="font-size:9px;">Baru</span>
                    @endif
                </td>

                {{-- Isi Notifikasi --}}
                <td>
                    <div class="d-flex align-items-start gap-2">
                        <div class="bg-{{ $bsColor }} bg-opacity-10 text-{{ $bsColor }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:38px;height:38px;">
                            <i class="fas {{ $icon }} fa-sm"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold {{ $isRead ? 'text-muted' : 'text-dark' }}">
                                {{ $judul }}
                            </div>
                            <div class="text-muted small mt-1" style="max-width:380px;white-space:pre-line;">
                                {{ Str::limit($pesan, 120) }}
                            </div>
                            @if($urlAksi)
                                <a href="{{ $urlAksi }}" class="btn btn-sm btn-outline-primary mt-1 py-0 px-2" style="font-size:11px;">
                                    <i class="fas fa-external-link-alt me-1"></i>Lihat Detail
                                </a>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- Tujuan / Pengirim — menjelaskan notifikasi ini untuk apa --}}
                <td>
                    <span class="badge bg-{{ $tujuan['badge'] }} bg-opacity-10 text-{{ $tujuan['badge'] }} border border-{{ $tujuan['badge'] }} border-opacity-25 px-2 py-1 d-inline-flex align-items-center gap-1"
                          style="font-size:11px;">
                        <i class="fas {{ $tujuan['icon'] }}"></i>
                        {{ $tujuan['label'] }}
                    </span>
                    @if($notification->sender)
                        <div class="text-muted mt-1" style="font-size:10px;">
                            <i class="fas fa-user-shield me-1"></i>{{ $notification->sender->name ?? 'Admin' }}
                        </div>
                    @endif
                </td>

                {{-- Waktu --}}
                <td>
                    <div class="small text-muted">
                        {{ $createdAt ? $createdAt->diffForHumans() : '-' }}
                    </div>
                    <div class="text-muted" style="font-size:10px;">
                        {{ $createdAt ? $createdAt->format('d/m/Y H:i') : '' }}
                    </div>
                </td>

                {{-- Aksi --}}
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        @if(!$isRead)
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-mark-read"
                                    onclick="markAsRead({{ $notification->id }})"
                                    title="Tandai sudah dibaca">
                                <i class="fas fa-check"></i>
                            </button>
                        @endif
                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                onclick="deleteNotification({{ $notification->id }})"
                                title="Hapus notifikasi">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="text-center py-5">
    <i class="fas fa-bell-slash fa-3x text-muted mb-3 d-block"></i>
    <h6 class="text-muted">Tidak ada notifikasi</h6>
    <p class="text-muted small">Belum ada notifikasi untuk ditampilkan di kategori ini.</p>
</div>
@endif
