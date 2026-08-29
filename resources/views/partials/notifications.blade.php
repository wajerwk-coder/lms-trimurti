<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button"
       data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        @if(isset($unreadCount) && $unreadCount > 0)
            <span class="badge bg-danger badge-counter">{{ $unreadCount }}</span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end dropdown-notifications"
        aria-labelledby="notificationsDropdown"
        role="status"
        aria-live="polite">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            Notifikasi
            @if(isset($unreadCount) && $unreadCount > 0)
                <span class="badge bg-primary">{{ $unreadCount }} Baru</span>
            @endif
        </li>

        @if(isset($notifications) && $notifications->count() > 0)
            @foreach($notifications as $notification)
                <li>
                    <a class="dropdown-item d-flex align-items-center notification-link"
                       href="{{ $notification->action_url ?? '#' }}"
                       title="{{ $notification->title }}"
                       @if($notification->action_url && filter_var($notification->action_url, FILTER_VALIDATE_URL)) target="_blank" rel="noopener noreferrer" @endif>
                        <div class="me-3">
                            <div class="icon-circle bg-{{ $notification->tipe ?: 'secondary' }}">
                                <i class="fas fa-{{ $notification->icon ?: 'bell' }} text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                            <div class="fw-bold text-sm">{{ $notification->title }}</div>
                            <span class="{{ $notification->read_at ? '' : 'fw-bold' }}">
                                {{ $notification->message }}
                            </span>
                        </div>
                    </a>
                </li>
                @if(!$loop->last)
                    <li><hr class="dropdown-divider"></li>
                @endif
            @endforeach
            <li class="dropdown-footer d-flex justify-content-between align-items-center">
                <a class="text-center" href="#" onclick="alert('Fitur ini akan segera tersedia')">Lihat Semua Notifikasi</a>
                @if(isset($unreadCount) && $unreadCount > 0)
                    <a href="#" onclick="alert('Fitur ini akan segera tersedia')" class="text-sm text-primary">Tandai semua sudah dibaca</a>
                @endif
            </li>
        @else
            <li class="dropdown-item text-center py-3">
                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">Tidak ada notifikasi</p>
            </li>
        @endif
    </ul>
</li>
