<header class="top-header d-flex align-items-center justify-content-between px-3 px-md-4">

    {{-- KIRI: Toggle + Panel Label --}}
    <div class="d-flex align-items-center gap-2">
        <button type="button" id="sidebarToggle" class="btn-icon d-none d-md-flex" title="Toggle Sidebar" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <button type="button" id="mobileSidebarToggle" class="btn-icon d-md-none" title="Menu" aria-label="Buka Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="d-none d-lg-block">
            <span class="badge fw-semibold px-3 py-2"
                  style="background:linear-gradient(135deg,#7c3aed,#db2777);color:#fff;border-radius:8px;">
                <i class="fas fa-user-graduate me-1"></i> Portal Siswa
            </span>
        </div>
    </div>

    {{-- TENGAH: Search --}}
    <div class="d-none d-lg-flex align-items-center flex-grow-1 mx-4" style="max-width: 420px;">
        <form id="globalSearchForm" method="GET" action="{{ route('siswa.materials.index') }}" class="w-100">
            <div class="input-group input-group-sm header-search">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input id="globalSearch" type="text" name="search" class="form-control border-start-0 bg-transparent"
                       placeholder="Cari materi, tugas, nilai..." autocomplete="off"
                       value="{{ request('search') }}">
            </div>
        </form>
    </div>

    {{-- KANAN: Notifikasi + User --}}
    <div class="d-flex align-items-center gap-2 gap-md-3">

        {{-- Upcoming Deadlines Quick Access --}}
        <a href="{{ route('siswa.assignments.index') }}" class="btn-icon d-none d-md-flex text-decoration-none" title="Tugas">
            <i class="fas fa-tasks text-primary"></i>
        </a>

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
                        <button class="btn btn-link btn-sm p-0 text-muted text-decoration-none siswa-mark-all">
                            <small>Tandai dibaca</small>
                        </button>
                    </div>
                </div>
                <div style="max-height:300px;overflow-y:auto;">
                    @forelse($notifications ?? [] as $notif)
                        <a class="dropdown-item notif-item d-flex align-items-start gap-2 py-2 px-3 {{ is_null($notif->read_at) ? 'notif-unread' : '' }}"
                           href="{{ $notif->url_aksi ?? $notif->action_url ?? '#' }}">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-info bg-opacity-10 text-info"
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
            <button class="btn p-0 d-flex align-items-center gap-2 border-0 bg-transparent"
                    style="min-width:0;"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                @php $siswaProfile = Auth::user()->siswaProfile; @endphp

                {{-- Avatar --}}
                <div class="flex-shrink-0 position-relative">
                    @php
                        // Ambil fresh dari DB untuk dapat foto terbaru (Cloudinary URL)
                        $siswaAvatarSrc = (Auth::user()->fresh() ?? Auth::user())->photo_url;
                    @endphp
                    <img src="{{ $siswaAvatarSrc }}"
                         alt="Avatar"
                         class="rounded-circle border border-2 shadow-sm"
                         style="width:38px;height:38px;object-fit:cover;border-color:rgba(124,58,237,.3)!important;"
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=7c3aed&color=fff'">
                    {{-- Online dot --}}
                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-white"
                          style="width:10px;height:10px;"></span>
                </div>

                {{-- Name + Role (desktop only) --}}
                <div class="d-none d-md-block text-start" style="max-width:130px;">
                    <div class="fw-semibold text-dark lh-1 mb-1"
                         style="font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="lh-1" style="font-size:.7rem;color:#7c3aed;font-weight:600;white-space:nowrap;">
                        {{ $siswaProfile?->kelas?->name ?? 'Siswa' }}
                    </div>
                </div>

                <i class="fas fa-chevron-down d-none d-md-inline text-muted ms-1" style="font-size:.6rem;"></i>
            </button>

            {{-- Dropdown --}}
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 py-2"
                style="min-width:220px;border-radius:14px;box-shadow:0 8px 30px rgba(124,58,237,.15)!important;">
                {{-- Header profil --}}
                <li>
                    <div class="px-3 pb-2 pt-1 mb-1 border-bottom d-flex align-items-center gap-2">
                        <img src="{{ $siswaAvatarSrc }}"
                             class="rounded-circle flex-shrink-0"
                             style="width:42px;height:42px;object-fit:cover;"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=7c3aed&color=fff'">
                        <div style="min-width:0;">
                            <div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">
                                {{ Auth::user()->name }}
                            </div>
                            <div class="text-truncate" style="font-size:.72rem;color:#7c3aed;">
                                {{ $siswaProfile?->nis ? 'NIS: '.$siswaProfile->nis : Auth::user()->email }}
                            </div>
                        </div>
                    </div>
                </li>

                <li><hr class="dropdown-divider mx-2 my-1"></li>

                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-danger"
                       href="#"
                       onclick="event.preventDefault(); document.getElementById('siswa-logout-form').submit();">
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

<form id="siswa-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

<style>
.top-header {
    background: #fff;
    height: var(--hdr-height, 64px);
    position: relative; z-index: 1;
    border-bottom: 2px solid transparent;
    background-image: linear-gradient(white, white),
                      linear-gradient(90deg, #7c3aed, #a21caf, #db2777);
    background-origin: border-box;
    background-clip: padding-box, border-box;
    box-shadow: 0 2px 12px rgba(124,58,237,.08);
}
.btn-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border: none; background: transparent;
    border-radius: 10px; color: #64748b; cursor: pointer;
    transition: background .2s, color .2s; font-size: 1rem; padding: 0;
}
.btn-icon:hover {
    background: linear-gradient(135deg, rgba(124,58,237,.1), rgba(219,39,119,.1));
    color: #a21caf;
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
    background: #f3f4f6; border: 1.5px solid #d1d5db;
    border-radius: 20px; overflow: hidden; transition: border-color .2s, box-shadow .2s;
}
.header-search .input-group:focus-within {
    border-color: #a21caf; background: #fff;
    box-shadow: 0 0 0 3px rgba(162,28,175,.12);
}
.header-search .form-control, .header-search .input-group-text {
    background: transparent; border: none; box-shadow: none; color: #374151;
}
.header-search .form-control::placeholder { color: #9ca3af; }
.notif-dropdown {
    border: 1px solid #e8edf2; border-radius: 12px;
    min-width: 320px; padding: 0; overflow: hidden;
    box-shadow: 0 8px 30px rgba(124,58,237,.12);
}
.notif-item { border-bottom: 1px solid #f1f5f9; border-radius: 0; }
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #fdf4ff; }
.notif-unread { background: #fae8ff; }
.dropdown-menu {
    border: 1px solid #e8edf2; border-radius: 10px;
    box-shadow: 0 8px 24px rgba(124,58,237,.1);
}
.dropdown-item {
    border-radius: 8px; margin: 0 6px;
    padding: .5rem .75rem; font-size: .875rem;
    width: calc(100% - 12px);
}
.dropdown-item:hover { background: #fdf4ff; color: #7c3aed; }
.dropdown-item.text-danger:hover { background: #fef2f2; color: #dc2626 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const markAllBtn = document.querySelector('.siswa-mark-all');
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

    // Search suggestions (simple)
    const searchInput = document.getElementById('globalSearch');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') this.blur();
        });
    }
});
</script>
