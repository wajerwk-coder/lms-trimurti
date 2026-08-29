@extends('layouts.guru')

@section('title', 'Manajemen Tugas - LMS Trimurti Husada')
@section('page-title', 'Manajemen Tugas')
@section('page-subtitle', 'Kelola tugas dan penugasan untuk siswa')

@section('page-actions')
<a href="{{ route('guru.assignments.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-2"></i>Buat Tugas
</a>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Manajemen Tugas</li>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Enhanced Tab Navigation -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <ul class="nav nav-pills nav-justified nav-pills-custom" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $tab === 'active' ? 'active' : '' }} d-flex align-items-center justify-content-center" 
                           href="{{ route('guru.assignments.index', ['tab' => 'active'] + request()->except('tab')) }}"
                           role="tab">
                            <i class="fas fa-play me-2"></i>
                            <div class="text-start">
                                <div class="fw-bold">Tugas Aktif</div>
                                <small class="opacity-75">Sedang berjalan</small>
                            </div>
                            <span class="badge bg-white text-primary ms-2 fw-bold">{{ $totalStats['active_assignments'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $tab === 'history' ? 'active' : '' }} d-flex align-items-center justify-content-center" 
                           href="{{ route('guru.assignments.index', ['tab' => 'history'] + request()->except('tab')) }}"
                           role="tab">
                            <i class="fas fa-history me-2"></i>
                            <div class="text-start">
                                <div class="fw-bold">Riwayat Tugas</div>
                                <small class="opacity-75">Semua tugas</small>
                            </div>
                            <span class="badge bg-white text-primary ms-2 fw-bold">{{ $totalStats['total_assignments'] ?? 0 }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-lg-4">
                <div class="text-lg-end text-center mt-3 mt-lg-0">
                    <div class="d-flex align-items-center justify-content-lg-end justify-content-center gap-3">
                        <div class="text-center">
                            <div class="text-primary fw-bold h5 mb-0">{{ $totalStats['total_submissions'] ?? 0 }}</div>
                            <small class="text-muted">Total Pengumpulan</small>
                        </div>
                        <div class="vr d-none d-lg-block"></div>
                        <div class="text-center">
                            <div class="text-success fw-bold h5 mb-0">{{ $totalStats['graded_submissions'] ?? 0 }}</div>
                            <small class="text-muted">Sudah Dinilai</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($tab === 'history')
<!-- Statistics Cards for History Tab -->
<div class="row g-4 mb-4 fade-in">
    <div class="col-xl-3 col-md-6">
        <div class="stats-card card border-0 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-tasks fa-2x opacity-75"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small opacity-75 text-uppercase fw-medium">Total Tugas</div>
                        <div class="h3 mb-0 fw-bold">{{ $totalStats['total_assignments'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stats-card card border-0 bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-play fa-2x opacity-75"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small opacity-75 text-uppercase fw-medium">Tugas Aktif</div>
                        <div class="h3 mb-0 fw-bold">{{ $totalStats['active_assignments'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stats-card card border-0 bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-upload fa-2x opacity-75"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small opacity-75 text-uppercase fw-medium">Total Pengumpulan</div>
                        <div class="h3 mb-0 fw-bold">{{ $totalStats['total_submissions'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stats-card card border-0 bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-star fa-2x opacity-50"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small opacity-75 text-uppercase fw-medium">Sudah Dinilai</div>
                        <div class="h3 mb-0 fw-bold">{{ $totalStats['graded_submissions'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-lg">
    <!-- Enhanced Header with Better Visual Hierarchy -->
    <div class="card-header border-0 bg-gradient-primary text-white position-relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="position-absolute top-0 end-0 opacity-10">
            <i class="fas fa-{{ $tab === 'history' ? 'history' : 'tasks' }} fa-6x"></i>
        </div>
        
        <div class="position-relative">
            <div class="row align-items-center py-2">
                <div class="col-lg-8 col-md-7">
                    <div class="d-flex align-items-center">
                        <!-- Icon Container -->
                        <div class="bg-white bg-opacity-20 rounded-3 p-3 me-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-{{ $tab === 'history' ? 'history' : 'list' }} text-white fa-lg"></i>
                        </div>
                        
                        <!-- Title Container -->
                        <div class="flex-grow-1">
                            <h4 class="mb-1 fw-bold">{{ $tab === 'history' ? 'Riwayat Tugas' : 'Daftar Tugas Aktif' }}</h4>
                            <div class="d-flex align-items-center gap-3">
                                <span class="opacity-90">
                                    <i class="fas fa-chart-bar me-1"></i>
                                    {{ $assignments->total() ?? 0 }} tugas ditemukan
                                </span>
                                @if($tab === 'active')
                                <span class="opacity-90">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $assignments->where('due_date', '<=', now()->addDay())->count() }} deadline dekat
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-5">
                    <div class="text-end">
                        <!-- Quick Stats Cards -->
                        <div class="row g-2">
                            @if($tab === 'active')
                            <div class="col-6">
                                <div class="bg-white bg-opacity-15 rounded-3 p-2 text-center">
                                    <div class="h5 mb-0 fw-bold">{{ $totalStats['active_assignments'] ?? 0 }}</div>
                                    <small class="opacity-90">Aktif</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white bg-opacity-15 rounded-3 p-2 text-center">
                                    <div class="h5 mb-0 fw-bold">{{ $totalStats['total_submissions'] ?? 0 }}</div>
                                    <small class="opacity-90">Pengumpulan</small>
                                </div>
                            </div>
                            @else
                            <div class="col-4">
                                <div class="bg-white bg-opacity-15 rounded-3 p-2 text-center">
                                    <div class="h6 mb-0 fw-bold">{{ $totalStats['total_assignments'] ?? 0 }}</div>
                                    <small class="opacity-90 small">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-white bg-opacity-15 rounded-3 p-2 text-center">
                                    <div class="h6 mb-0 fw-bold">{{ $totalStats['total_submissions'] ?? 0 }}</div>
                                    <small class="opacity-90 small">Submit</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-white bg-opacity-15 rounded-3 p-2 text-center">
                                    <div class="h6 mb-0 fw-bold">{{ $totalStats['graded_submissions'] ?? 0 }}</div>
                                    <small class="opacity-90 small">Dinilai</small>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-3 d-flex gap-2 justify-content-end">
                            <button class="btn btn-light btn-sm rounded-pill px-3" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#filterCollapse" 
                                    aria-expanded="{{ request()->hasAny(['subject_id', 'class', 'status', 'period']) ? 'true' : 'false' }}" 
                                    aria-controls="filterCollapse" 
                                    title="Toggle Filter">
                                <i class="fas fa-filter text-primary me-1"></i>
                                <span class="d-none d-sm-inline">Filter</span>
                            </button>
                            
                            @if($tab === 'active')
                            <button class="btn btn-warning btn-sm rounded-pill px-3" id="showDeadlineAlert">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span class="d-none d-sm-inline">Deadline</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Collapsible Filter -->
    <div class="collapse {{ request()->hasAny(['subject_id', 'class', 'status', 'period']) ? 'show' : '' }}" id="filterCollapse">
        <div class="card-body border-bottom bg-light">
            <form method="GET" action="{{ route('guru.assignments.index') }}" id="filterForm">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small fw-medium text-primary">
                            <i class="fas fa-book me-1"></i>Mata Pelajaran
                        </label>
                        <select name="subject_id" class="form-select form-select-sm">
                            <option value="">ðŸ” Semua Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small fw-medium text-primary">
                            <i class="fas fa-users me-1"></i>Kelas
                        </label>
                        <select name="class_id" class="form-select form-select-sm">
                            <option value="">ðŸ” Semua Kelas</option>
                            @if(isset($classes))
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    ï¿½ {{ $class->name }}
                                </option>
                                @endforeach
                            @else
                                <option value="1" {{ request('class_id') == '1' ? 'selected' : '' }}>ðŸ“š Kelas X Keperawatan</option>
                            @endif
                        </select>
                    </div>
                    
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small fw-medium text-primary">
                            <i class="fas fa-flag me-1"></i>Status
                        </label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">ðŸ” Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>âœ… Aktif</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>âœ”ï¸ Selesai</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>ðŸ“ Draft</option>
                        </select>
                    </div>
                    
                    @if($tab === 'history')
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small fw-medium text-primary">
                            <i class="fas fa-calendar me-1"></i>Periode
                        </label>
                        <select name="period" class="form-select form-select-sm">
                            <option value="">ðŸ” Semua Waktu</option>
                            <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>ðŸ“… Minggu Ini</option>
                            <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>ðŸ“Š Bulan Ini</option>
                            <option value="semester" {{ request('period') == 'semester' ? 'selected' : '' }}>ðŸ“ˆ Semester Ini</option>
                        </select>
                    </div>
                    @endif
                    
                    <div class="col-lg-{{ $tab === 'history' ? '3' : '5' }} col-md-12">
                        <label class="form-label small fw-medium">&nbsp;</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fas fa-search me-1"></i>Cari Tugas
                            </button>
                            <a href="{{ route('guru.assignments.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm px-3">
                                <i class="fas fa-refresh me-1"></i>Reset
                            </a>
                            @if($tab === 'active')
                            <button type="button" class="btn btn-outline-warning btn-sm px-3" id="showDeadlineAlert">
                                <i class="fas fa-exclamation-triangle me-1"></i>Deadline Dekat
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <!-- Enhanced Table Container -->
        <div class="table-responsive">
            <table class="table table-hover mb-0 assignment-table">
                <thead class="table-header-custom">
                    <tr>
                        <th class="border-0 fw-bold text-uppercase">
                            <i class="fas fa-tasks text-primary me-2"></i>
                            Tugas
                        </th>
                        <th class="border-0 fw-bold text-uppercase">
                            <i class="fas fa-book text-primary me-2"></i>
                            Mata Pelajaran
                        </th>
                        <th class="border-0 fw-bold text-uppercase text-center">
                            <i class="fas fa-users text-primary me-2"></i>
                            Kelas
                        </th>
                        @if($tab === 'history')
                        <th class="border-0 fw-bold text-uppercase text-center">
                            <i class="fas fa-calendar-plus text-primary me-2"></i>
                            Dibuat
                        </th>
                        @endif
                        <th class="border-0 fw-bold text-uppercase text-center">
                            <i class="fas fa-clock text-primary me-2"></i>
                            Deadline
                        </th>
                        <th class="border-0 fw-bold text-uppercase text-center">
                            <i class="fas fa-flag text-primary me-2"></i>
                            Status
                        </th>
                        @if($tab === 'history')
                        <th class="border-0 fw-bold text-uppercase text-center">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Statistik
                        </th>
                        @else
                        <th class="border-0 fw-bold text-uppercase text-center">
                            <i class="fas fa-file-upload text-primary me-2"></i>
                            Pengumpulan
                        </th>
                        @endif
                        <th class="border-0 fw-bold text-uppercase text-center">
                            <i class="fas fa-cog text-primary me-2"></i>
                            Aksi
                        </th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                        <tr data-subject-id="{{ $assignment->subject_id }}">
                            <td>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-medium">{{ $assignment->title }}</h6>
                                        <div class="text-muted small">
                                            {{ \Illuminate\Support\Str::limit($assignment->description, $tab === 'history' ? 60 : 50) }}
                                        </div>
                                        @if($assignment->file_url)
                                        <div class="mt-1">
                                            <small class="text-info">
                                                <i class="fas fa-paperclip me-1"></i>
                                                File lampiran
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @php
    $classSubjectData = $assignment->getClassSubject();
    $subjectName = $classSubjectData->subject_name ?? 'N/A';
    $className = $classSubjectData->class_name ?? 'N/A';
@endphp

                            <td class="text-nowrap">
                                <span class="badge bg-light text-dark border">
                                    {{ $subjectName }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">
                                    {{ $className }}
                                </span>
                            </td>
                            
                            @if($tab === 'history')
                            <td class="text-muted small text-nowrap">
                                {{ $assignment->created_at->format('d/m/Y') }}
                                <div class="text-xs opacity-75">
                                    {{ $assignment->created_at->format('H:i') }}
                                </div>
                            </td>
                            @endif
                            
                            <td class="text-muted small text-nowrap">
                                @php $dueDate = $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date) : null; @endphp
                                @if($dueDate)
                                    {{ $dueDate->format('d/m/Y') }}
                                    <div class="text-xs opacity-75">
                                        {{ $dueDate->format('H:i') }}
                                    </div>
                                    @if($tab === 'history' && $dueDate->isPast())
                                    <span class="badge bg-danger text-white small mt-1">Terlewat</span>
                                    @elseif($tab === 'history' && $dueDate->diffInDays() <= 1)
                                    <span class="badge bg-warning text-dark small mt-1">Urgent</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            
                            <td>
                                @php
                                    $statusClass = 'secondary';
                                    $statusText = 'Draft';
                                    if ($assignment->is_published) {
                                        if ($dueDate && $dueDate->isPast()) {
                                            $statusClass = 'primary';
                                            $statusText = 'Selesai';
                                        } else {
                                            $statusClass = 'success';
                                            $statusText = 'Aktif';
                                        }
                                    }
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                            
                            @if($tab === 'history')
                            <td>
                                <div class="small">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Pengumpulan:</span>
                                        <strong>{{ $assignment->submissions_count }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Dinilai:</span>
                                        <strong class="text-success">{{ $assignment->graded_count }}</strong>
                                    </div>
                                    @if($assignment->ungraded_count > 0)
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Belum:</span>
                                        <strong class="text-danger">{{ $assignment->ungraded_count }}</strong>
                                    </div>
                                    @endif
                                    @if($assignment->average_score)
                                    <div class="d-flex justify-content-between">
                                        <span>Rata-rata:</span>
                                        <strong class="text-primary">{{ $assignment->average_score }}</strong>
                                    </div>
                                    @endif
                                    @if($assignment->completion_rate > 0)
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: {{ $assignment->completion_rate }}%"
                                             title="Tingkat penyelesaian: {{ $assignment->completion_rate }}%">
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            @else
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="text-success fw-medium">{{ $assignment->submissions_count ?? 0 }}</span>
                                    @if(isset($assignment->ungraded_count) && $assignment->ungraded_count > 0)
                                    <span class="text-muted mx-1">|</span>
                                    <span class="text-danger">{{ $assignment->ungraded_count }} belum dinilai</span>
                                    @endif
                                </div>
                            </td>
                            @endif
                            
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('guru.assignments.show', $assignment->id) }}"
                                       class="btn btn-outline-primary btn-sm"
                                       title="Lihat detail tugas">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('guru.assignments.edit', $assignment->id) }}"
                                       class="btn btn-outline-secondary btn-sm"
                                       title="Edit tugas">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($assignment->submissions_count > 0)
                                    <a href="{{ route('guru.assignments.submissions', $assignment->id) }}" 
                                       class="btn btn-outline-info btn-sm"
                                       title="Lihat Pengumpulan">
                                        <i class="fas fa-file-upload"></i>
                                    </a>
                                    @endif
                                    @if($tab !== 'history' || ($tab === 'history' && $assignment->submissions_count == 0))
                                    <form action="{{ route('guru.assignments.destroy', $assignment->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')"
                                                title="Hapus tugas">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $tab === 'history' ? '8' : '7' }}" class="border-0 p-0">
                                <div class="empty-state-container">
                                    <div class="empty-state-content text-center py-5">
                                        <!-- Animated Icon -->
                                        <div class="empty-icon-wrapper mb-4">
                                            <div class="empty-icon-bg">
                                                <i class="fas fa-{{ $tab === 'history' ? 'history' : 'tasks' }} fa-4x text-white"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Content -->
                                        <h3 class="text-dark mb-3 fw-bold">{{ $tab === 'history' ? 'Tidak ada riwayat tugas' : 'Belum ada tugas aktif' }}</h3>
                                        <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                                            {{ $tab === 'history' ? 'Tugas yang Anda buat akan muncul di sini dengan statistik lengkap untuk analisis pembelajaran' : 'Mulai dengan membuat tugas pertama Anda untuk memberikan pembelajaran yang interaktif kepada siswa.' }}
                                        </p>
                                        
                                        <!-- Actions -->
                                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                                            @if($tab !== 'history')
                                            <a href="{{ route('guru.assignments.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                                                <i class="fas fa-plus me-2"></i>Buat Tugas Pertama
                                            </a>
                                            <button class="btn btn-outline-primary btn-lg rounded-pill px-4" onclick="showTutorial()">
                                                <i class="fas fa-question-circle me-2"></i>Pelajari Cara
                                            </button>
                                            @else
                                            <a href="{{ route('guru.assignments.index', ['tab' => 'active']) }}" class="btn btn-primary rounded-pill px-4">
                                                <i class="fas fa-arrow-left me-2"></i>Lihat Tugas Aktif
                                            </a>
                                            <a href="{{ route('guru.assignments.create') }}" class="btn btn-outline-primary rounded-pill px-4">
                                                <i class="fas fa-plus me-2"></i>Buat Tugas Baru
                                            </a>
                                            @endif
                                        </div>
                                        
                                        <!-- Quick Tips -->
                                        @if($tab !== 'history')
                                        <div class="mt-4 pt-3 border-top">
                                            <small class="text-muted">
                                                <i class="fas fa-lightbulb me-1"></i>
                                                Tips: Gunakan menu "Tambah Baru" di header untuk membuat tugas dengan cepat
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

                @if($assignments->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $assignments->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Clear all floats before end of content -->
<div class="clearfix"></div>
<div style="clear: both; height: 1px; overflow: hidden;"></div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Tutorial popup ───────────────────────────────────────────────────
    window.showTutorial = function () {
        alert(
            'Panduan Manajemen Tugas:\n\n' +
            '1. Klik "Tambah Tugas" untuk membuat tugas baru.\n' +
            '2. Isi judul, deskripsi, mata pelajaran, dan batas waktu.\n' +
            '3. Centang "Publikasikan" agar siswa bisa melihat tugas.\n' +
            '4. Klik ikon mata untuk melihat detail dan pengumpulan.\n' +
            '5. Klik "Nilai" pada pengumpulan siswa untuk memberi nilai.'
        );
    };

    // ── Search ───────────────────────────────────────────────────────────
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.assignment-row').forEach(function (row) {
                row.style.display = (q === '' || row.textContent.toLowerCase().includes(q)) ? '' : 'none';
            });
        });
    }

    // ── Checkbox bulk select ─────────────────────────────────────────────
    const selectAll = document.getElementById('selectAllAssignments');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.assignment-checkbox').forEach(cb => cb.checked = this.checked);
        });
    }
});
</script>
@endpush

@endsection