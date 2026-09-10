@php
    $userRole = auth()->user()->role ?? 'siswa';
    $layout = match($userRole) {
        'admin' => 'layouts.admin',
        'guru'  => 'layouts.guru',
        default => 'layouts.siswa',
    };
@endphp

@extends($layout)

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Pusat notifikasi sistem')

@section('content')
<div class="container-fluid px-0">

    {{-- Header Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="fas fa-bell text-primary me-2"></i>Notifikasi
                    </h5>
                    <p class="text-muted mb-0 small">Pusat notifikasi dan pengumuman sistem</p>
                </div>
                <div class="d-flex gap-2">
                    @if($notifications->where('read_at', null)->count() > 0)
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                            <i class="fas fa-check-double me-1"></i>Tandai Semua Dibaca
                        </button>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <ul class="nav nav-tabs px-3 pt-2" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-all" role="tab">
                        <i class="fas fa-bell me-1"></i>Semua
                        <span class="badge bg-primary ms-1">{{ $notifications->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-unread" role="tab">
                        <i class="fas fa-envelope me-1"></i>Belum Dibaca
                        @php $unreadCount = $notifications->getCollection()->filter(fn($n) => is_null($n->read_at))->count(); @endphp
                        @if($unreadCount > 0)
                            <span class="badge bg-danger ms-1">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-pengumuman" role="tab">
                        <i class="fas fa-bullhorn me-1"></i>Pengumuman
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-ujian" role="tab">
                        <i class="fas fa-calendar-check me-1"></i>Ujian
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Notifications Content --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="tab-content">

                {{-- Semua --}}
                <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
                    @include('partials.notification-list', [
                        'notifications' => $notifications->getCollection(),
                        'showAll'       => true,
                    ])
                </div>

                {{-- Belum Dibaca --}}
                <div class="tab-pane fade" id="tab-unread" role="tabpanel">
                    @include('partials.notification-list', [
                        'notifications' => $notifications->getCollection()->filter(fn($n) => is_null($n->read_at)),
                        'showAll'       => false,
                    ])
                </div>

                {{-- Pengumuman --}}
                <div class="tab-pane fade" id="tab-pengumuman" role="tabpanel">
                    @include('partials.notification-list', [
                        'notifications' => $notifications->getCollection()->filter(fn($n) => in_array($n->tipe ?? $n->type ?? '', ['pengumuman', 'info'])),
                        'showAll'       => false,
                    ])
                </div>

                {{-- Ujian --}}
                <div class="tab-pane fade" id="tab-ujian" role="tabpanel">
                    @include('partials.notification-list', [
                        'notifications' => $notifications->getCollection()->filter(fn($n) => in_array($n->tipe ?? $n->type ?? '', ['exam', 'exam_schedule', 'ujian'])),
                        'showAll'       => false,
                    ])
                </div>

            </div>
        </div>

        @if($notifications->hasPages())
        <div class="card-footer bg-white">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>

</div>

<script>
function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
}

function markAsRead(id) {
    fetch(`/notifications/${id}/mark-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById(`notification-${id}`);
            if (row) {
                row.classList.remove('table-warning');
                row.querySelector('.unread-badge')?.remove();
                row.querySelector('.btn-mark-read')?.remove();
            }
        }
    });
}

function deleteNotification(id) {
    if (!confirm('Hapus notifikasi ini?')) return;
    fetch(`/notifications/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`notification-${id}`)?.remove();
        }
    });
}
</script>
@endsection
