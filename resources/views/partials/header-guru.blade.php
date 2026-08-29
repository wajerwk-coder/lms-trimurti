<header class="top-header d-flex align-items-center justify-content-between px-3 px-md-4">

    {{-- KIRI: Toggle + Panel Label --}}
    <div class="d-flex align-items-center gap-2">
        <button id="sidebarToggle" class="btn-icon d-none d-md-flex" title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <button id="mobileSidebarToggle" class="btn-icon d-md-none" title="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="d-none d-lg-block">
            <span class="badge fw-semibold px-3 py-2"
                  style="background:linear-gradient(135deg,#0f766e,#0891b2);color:#fff;border-radius:8px;">
                <i class="fas fa-chalkboard-teacher me-1"></i> Panel Guru
            </span>
        </div>
    </div>

    {{-- TENGAH: Search (Desktop) --}}
    <div class="d-none d-lg-flex align-items-center flex-grow-1 mx-4" style="max-width: 420px;">
        <form id="globalSearchForm" method="GET" action="#" class="w-100">
            <div class="input-group input-group-sm header-search">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input id="globalSearch" type="text" class="form-control border-start-0 bg-transparent"
                       placeholder="Cari materi, tugas, siswa..." autocomplete="off">
            </div>
        </form>
    </div>

    {{-- KANAN: Notifikasi + Stats + User --}}
    <div class="d-flex align-items-center gap-2 gap-md-3">

        {{-- Pending Grading Badge --}}
        @if(isset($stats['pending_grading']) && $stats['pending_grading'] > 0)
        <a href="{{ route('guru.penilaian.index') }}" class="d-none d-md-flex btn-icon position-relative text-decoration-none" title="Tugas belum dinilai">
            <i class="fas fa-star text-warning"></i>
            <span class="notif-dot">{{ $stats['pending_grading'] }}</span>
        </a>
        @endif

        {{-- Notifikasi --}}
        <div class="dropdown">
            <button class="btn-icon position-relative" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi">
                <i class="fas fa-bell"></i>
                @if(isset($unreadCount) && $unreadCount > 0)
                    <span class="notif-dot">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end notif-dropdown shadow-lg">
                <div class="notif-header d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <span class="fw-bold text-dark">Notifikasi</span>
                    <div class="d-flex gap-2 align-items-center">
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                        @endif
                        <button class="btn btn-link btn-sm p-0 text-muted text-decoration-none guru-mark-all">
                            <small>Tandai dibaca</small>
                        </button>
                    </div>
                </div>
                <div style="max-height:300px;overflow-y:auto;">
                    @forelse($notifications ?? [] as $notif)
                        <a class="dropdown-item notif-item d-flex align-items-start gap-2 py-2 px-3 {{ is_null($notif->read_at) ? 'notif-unread' : '' }}"
                           href="{{ $notif->url_aksi ?? $notif->action_url ?? '#' }}">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary"
                                 style="width:34px;height:34px;">
                                <i class="fas fa-bell fa-xs"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-dark text-truncate" style="font-size:.83rem;">
                                    {{ $notif->judul ?? $notif->title ?? 'Notifikasi' }}
                                </div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    {{ $notif->created_at?->diffForHumans() ?? '' }}
                                </div>
                            </div>
                            @if(is_null($notif->read_at))
                                <span class="flex-shrink-0 bg-primary rounded-circle mt-2" style="width:7px;height:7px;display:inline-block;"></span>
                            @endif
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-bell-slash fa-2x mb-2 opacity-50"></i>
                            <p class="small mb-0">Tidak ada notifikasi</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-top px-3 py-2 text-center">
                    <a href="{{ route('notifications.index') }}" class="small fw-semibold text-primary text-decoration-none">
                        Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- User Menu --}}
        <div class="dropdown">
            @php
                $guruProfile = Auth::user()->guruProfile;
                // Foto disimpan di users_central.photo saat upload via profil
                // Fallback ke gurus.photo jika ada, lalu ui-avatars
                $guruPhotoSrc = Auth::user()->photo
                    ? asset('storage/'.Auth::user()->photo)
                    : ($guruProfile?->photo
                        ? asset('storage/'.$guruProfile->photo)
                        : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=0f766e&color=fff&size=64');
                $guruPhotoFallback = 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=0f766e&color=fff&size=64';
            @endphp
            <button class="btn p-0 d-flex align-items-center gap-2 border-0 bg-transparent"
                    style="min-width:0;"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false">

                {{-- Avatar + online dot --}}
                <div class="flex-shrink-0 position-relative">
                    <img src="{{ $guruPhotoSrc }}"
                         alt="Avatar"
                         class="rounded-circle border border-2 shadow-sm"
                         style="width:38px;height:38px;object-fit:cover;border-color:rgba(15,118,110,.3)!important;"
                         onerror="this.onerror=null;this.src='{{ $guruPhotoFallback }}'">
                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-white"
                          style="width:10px;height:10px;"></span>
                </div>

                {{-- Nama + Label Role --}}
                <div class="d-none d-md-block text-start" style="max-width:130px;">
                    <div class="fw-semibold text-dark lh-1 mb-1"
                         style="font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="lh-1" style="font-size:.7rem;color:#0f766e;font-weight:600;white-space:nowrap;">
                        {{ $guruProfile?->mata_pelajaran ?? 'Guru' }}
                    </div>
                </div>

                <i class="fas fa-chevron-down d-none d-md-inline text-muted ms-1" style="font-size:.6rem;"></i>
            </button>

            {{-- Dropdown --}}
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 py-2"
                style="min-width:220px;border-radius:14px;box-shadow:0 8px 30px rgba(15,118,110,.15)!important;">

                {{-- Header profil --}}
                <li>
                    <div class="px-3 pb-2 pt-1 mb-1 border-bottom d-flex align-items-center gap-2">
                        <img src="{{ $guruPhotoSrc }}"
                             class="rounded-circle flex-shrink-0"
                             style="width:42px;height:42px;object-fit:cover;"
                             onerror="this.onerror=null;this.src='{{ $guruPhotoFallback }}'">
                        <div style="min-width:0;">
                            <div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">
                                {{ Auth::user()->name }}
                            </div>
                            <div class="text-truncate" style="font-size:.72rem;color:#0f766e;">
                                {{ $guruProfile?->nip ? 'NIP: '.$guruProfile->nip : Auth::user()->email }}
                            </div>
                        </div>
                    </div>
                </li>

                <li><hr class="dropdown-divider mx-2 my-1"></li>

                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-danger"
                       href="#"
                       onclick="event.preventDefault(); document.getElementById('guru-logout-form').submit();">
                        <span class="rounded-2 bg-danger bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                              style="width:28px;height:28px;">
                            <i class="fas fa-sign-out-alt text-danger" style="font-size:.7rem;"></i>
                        </span>
                        <span>Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<form id="guru-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

<style>
.top-header {
    background: #fff;
    height: var(--hdr-height, 64px);
    position: relative; z-index: 1;
    border-bottom: 2px solid transparent;
    background-image: linear-gradient(white, white),
                      linear-gradient(90deg, #0f766e, #0891b2, #1d4ed8);
    background-origin: border-box;
    background-clip: padding-box, border-box;
    box-shadow: 0 2px 12px rgba(8,145,178,.08);
}
.btn-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border: none; background: transparent;
    border-radius: 10px; color: #64748b; cursor: pointer;
    transition: background .2s, color .2s; font-size: 1rem; padding: 0;
}
.btn-icon:hover {
    background: linear-gradient(135deg, rgba(15,118,110,.1), rgba(29,78,216,.1));
    color: #0891b2;
}
.notif-dot {
    position: absolute; top: -2px; right: -2px;
    min-width: 18px; height: 18px; border-radius: 9px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff; font-size: .65rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    padding: 0 4px; border: 2px solid #fff; line-height: 1;
}
.header-search .input-group {
    background: #f8fafc; border: 1.5px solid #e8edf2;
    border-radius: 20px; overflow: hidden; transition: border-color .2s;
}
.header-search .input-group:focus-within {
    border-color: #0891b2; background: #fff;
    box-shadow: 0 0 0 3px rgba(8,145,178,.12);
}
.header-search .form-control,
.header-search .input-group-text {
    background: transparent; border: none; box-shadow: none;
}
.notif-dropdown {
    border: 1px solid #e8edf2; border-radius: 12px;
    min-width: 320px; padding: 0; overflow: hidden;
    box-shadow: 0 8px 30px rgba(8,145,178,.12);
}
.notif-item { border-bottom: 1px solid #f1f5f9; border-radius: 0; }
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f0fdfa; }
.notif-unread { background: #ecfdf5; }
.dropdown-menu {
    border: 1px solid #e8edf2; border-radius: 10px;
    box-shadow: 0 8px 24px rgba(8,145,178,.1);
}
.dropdown-item {
    border-radius: 8px; margin: 0 6px;
    padding: .5rem .75rem; font-size: .875rem;
    width: calc(100% - 12px);
}
.dropdown-item:hover { background: #f0fdfa; color: #0f766e; }
.dropdown-item.text-danger:hover { background: #fef2f2; color: #dc2626 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const markAllBtn = document.querySelector('.guru-mark-all');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' }
            }).then(() => {
                document.querySelectorAll('.notif-unread').forEach(el => el.classList.remove('notif-unread'));
                document.querySelector('.notif-dot')?.remove();
            }).catch(() => {});
        });
    }
});
</script>
