<header class="top-header d-flex align-items-center justify-content-between px-3 px-md-4">

    {{-- KIRI: Toggle + Breadcrumb/Title --}}
    <div class="d-flex align-items-center gap-2">
        <button type="button" id="sidebarToggle" class="btn-icon d-none d-md-flex" title="Toggle Sidebar" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <button type="button" id="mobileSidebarToggle" class="btn-icon d-md-none" title="Menu" aria-label="Buka Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="d-none d-lg-block">
            <span class="badge fw-semibold px-3 py-2"
                  style="background:linear-gradient(135deg,#3b82f6,#6d28d9);color:#fff;border-radius:8px;">
                <i class="fas fa-shield-alt me-1"></i> Admin Panel
            </span>
        </div>
    </div>

    {{-- KANAN: Notifikasi + User --}}
    <div class="d-flex align-items-center gap-2 gap-md-3">

        {{-- Notifikasi --}}
        <div class="dropdown">
            <button class="btn-icon position-relative" type="button"
                    id="notifToggle" data-bs-toggle="dropdown"
                    aria-expanded="false" aria-label="Notifikasi">
                <i class="fas fa-bell"></i>
                @if(isset($unreadCount) && $unreadCount > 0)
                    <span class="notif-dot">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end notif-dropdown shadow-lg"
                 aria-labelledby="notifToggle">
                {{-- Header --}}
                <div class="notif-header d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <span class="fw-bold text-dark">Notifikasi</span>
                    <div class="d-flex gap-2 align-items-center">
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                        @endif
                        <button class="btn btn-link btn-sm p-0 text-muted text-decoration-none" id="adminMarkAllRead">
                            <small>Tandai semua dibaca</small>
                        </button>
                    </div>
                </div>
                {{-- List --}}
                <div class="notif-list" id="adminNotifList" style="max-height: 320px; overflow-y: auto;">
                    @forelse($notifications ?? [] as $notif)
                        <a class="dropdown-item notif-item d-flex align-items-start gap-2 py-2 px-3 {{ is_null($notif->read_at) ? 'notif-unread' : '' }}"
                           href="{{ $notif->url_aksi ?? $notif->action_url ?? '#' }}">
                            <div class="notif-icon-wrap rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                                @switch($notif->tipe ?? $notif->type ?? 'info')
                                    @case('exam') bg-warning bg-opacity-15 text-warning @break
                                    @case('warning') bg-danger bg-opacity-15 text-danger @break
                                    @case('success') bg-success bg-opacity-15 text-success @break
                                    @default bg-primary bg-opacity-15 text-primary
                                @endswitch"
                                 style="width:36px;height:36px;">
                                <i class="fas fa-{{ ($notif->tipe ?? '') === 'exam' ? 'calendar-check' : (($notif->tipe ?? '') === 'warning' ? 'exclamation-triangle' : 'bell') }} fa-sm"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-dark text-truncate" style="font-size:.83rem;">
                                    {{ $notif->judul ?? $notif->title ?? 'Notifikasi' }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size:.77rem;">
                                    {{ Str::limit($notif->pesan ?? $notif->message ?? '', 60) }}
                                </div>
                                <div class="text-muted" style="font-size:.72rem;">
                                    {{ $notif->created_at?->diffForHumans() ?? '' }}
                                </div>
                            </div>
                            @if(is_null($notif->read_at))
                                <span class="flex-shrink-0 bg-primary rounded-circle mt-1" style="width:7px;height:7px;display:inline-block;"></span>
                            @endif
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-bell-slash fa-2x mb-2 opacity-50"></i>
                            <p class="small mb-0">Tidak ada notifikasi</p>
                        </div>
                    @endforelse
                </div>
                {{-- Footer --}}
                <div class="border-top px-3 py-2 text-center">
                    <a href="{{ route('notifications.index') }}" class="small fw-semibold text-primary text-decoration-none">
                        Lihat Semua Notifikasi <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- User Menu --}}
        <div class="dropdown">
            @php
                $freshUser = Auth::user()->fresh() ?? Auth::user();
                $adminName  = $freshUser->name;
                $adminEmail = $freshUser->email;
                $adminPhoto = $freshUser->photo_url;
                $adminPhotoFallback = 'https://ui-avatars.com/api/?name='.urlencode($adminName).'&background=3b82f6&color=fff&size=64';
            @endphp
            <button class="btn p-0 d-flex align-items-center gap-2 border-0 bg-transparent"
                    style="min-width:0;"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false" id="adminUserMenu">

                {{-- Avatar + online dot --}}
                <div class="flex-shrink-0 position-relative">
                    <img src="{{ $adminPhoto }}"
                         alt="Avatar"
                         class="rounded-circle border border-2 shadow-sm"
                         style="width:38px;height:38px;object-fit:cover;border-color:rgba(59,130,246,.3)!important;"
                         onerror="this.src='{{ $adminPhotoFallback }}'">
                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-white"
                          style="width:10px;height:10px;"></span>
                </div>

                {{-- Nama + Label Role --}}
                <div class="d-none d-md-block text-start" style="max-width:130px;">
                    <div class="fw-semibold text-dark lh-1 mb-1"
                         style="font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $adminName }}
                    </div>
                    <div class="lh-1" style="font-size:.7rem;color:#6d28d9;font-weight:600;white-space:nowrap;">
                        Administrator
                    </div>
                </div>

                <i class="fas fa-chevron-down d-none d-md-inline text-muted ms-1" style="font-size:.6rem;"></i>
            </button>

            {{-- Dropdown --}}
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 py-2"
                style="min-width:220px;border-radius:14px;box-shadow:0 8px 30px rgba(99,102,241,.15)!important;">

                {{-- Header profil --}}
                <li>
                    <div class="px-3 pb-2 pt-1 mb-1 border-bottom d-flex align-items-center gap-2">
                        <img src="{{ $adminPhoto }}"
                             class="rounded-circle flex-shrink-0"
                             style="width:42px;height:42px;object-fit:cover;"
                             onerror="this.src='{{ $adminPhotoFallback }}'">
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($adminName) }}&background=3b82f6&color=fff'">
                        <div style="min-width:0;">
                            <div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">
                                {{ $adminName }}
                            </div>
                            <div class="text-truncate" style="font-size:.72rem;color:#6d28d9;">
                                {{ $adminEmail }}
                            </div>
                        </div>
                    </div>
                </li>

                <li><hr class="dropdown-divider mx-2 my-1"></li>

                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-danger"
                       href="#"
                       onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
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

<form id="admin-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

{{-- Header Styles --}}
<style>
.top-header {
    background: #fff;
    height: var(--hdr-height, 64px);
    position: relative;
    z-index: 1;
    border-bottom: 2px solid transparent;
    background-image: linear-gradient(white, white),
                      linear-gradient(90deg, #3b82f6, #6d28d9);
    background-origin: border-box;
    background-clip: padding-box, border-box;
    box-shadow: 0 2px 12px rgba(59,130,246,.08);
}

.btn-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border: none; background: transparent;
    border-radius: 10px; color: #64748b; cursor: pointer;
    transition: background .2s, color .2s; font-size: 1rem; padding: 0;
}
.btn-icon:hover {
    background: linear-gradient(135deg, rgba(59,130,246,.1), rgba(109,40,217,.1));
    color: #6366f1;
}

.notif-dot {
    position: absolute; top: -2px; right: -2px;
    min-width: 18px; height: 18px; border-radius: 9px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff; font-size: .65rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    padding: 0 4px; border: 2px solid #fff; line-height: 1;
}

.notif-dropdown {
    border: 1px solid #e8edf2; border-radius: 12px;
    min-width: 340px; max-width: 360px; padding: 0; overflow: hidden;
    box-shadow: 0 8px 30px rgba(99,102,241,.12);
}

.notif-item {
    border-radius: 0; transition: background .15s;
    border-bottom: 1px solid #f1f5f9;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f5f3ff; }
.notif-unread { background: #ede9fe; }

.dropdown-menu {
    border: 1px solid #e8edf2; border-radius: 10px;
    box-shadow: 0 8px 24px rgba(99,102,241,.1);
}
.dropdown-item {
    border-radius: 8px; margin: 0 6px;
    padding: .5rem .75rem; font-size: .875rem; transition: background .15s;
    width: calc(100% - 12px);
}
.dropdown-item:hover { background: #f5f3ff; color: #6366f1; }
.dropdown-item.text-danger:hover { background: #fef2f2; color: #dc2626 !important; }

@media (max-width: 576px) {
    .notif-dropdown { min-width: calc(100vw - 2rem); max-width: 100vw; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark all as read
    const markAllBtn = document.getElementById('adminMarkAllRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' }
            }).then(() => {
                document.querySelectorAll('.notif-unread').forEach(el => el.classList.remove('notif-unread'));
                document.querySelector('.notif-dot')?.remove();
                document.querySelector('#notifToggle .badge')?.remove();
                markAllBtn.textContent = 'Semua sudah dibaca';
            }).catch(() => {});
        });
    }
});
</script>
