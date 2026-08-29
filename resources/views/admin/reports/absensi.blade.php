@extends('layouts.admin')

@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')
@section('page-subtitle', 'Kehadiran siswa dan guru — SMK Kesehatan Trimurti Husada.')

@section('page-actions')
    <div class="dropdown">
        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-download fa-sm me-1"></i> Ekspor
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">PDF</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">Excel</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportReport('csv')">CSV</a></li>
        </ul>
    </div>
@endsection

@section('content')
<div>
    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 fw-semibold text-primary">Filter Laporan</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-muted"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Filter Actions:</div>
                    <a class="dropdown-item" href="#" onclick="resetFilters()">Reset Filter</a>
                    <a class="dropdown-item" href="#" onclick="saveFilter()">Simpan Filter</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="loadSavedFilter()">Muat Filter Tersimpan</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('admin.reports.attendance') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="user_type" class="form-label">Jenis Pengguna</label>
                    <select name="user_type" id="user_type" class="form-select">
                        <option value="all" {{ request('user_type') == 'all' ? 'selected' : '' }}>Semua Pengguna</option>
                        <option value="students" {{ request('user_type') == 'students' ? 'selected' : '' }}>Siswa</option>
                        <option value="teachers" {{ request('user_type') == 'teachers' ? 'selected' : '' }}>Guru</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="class" class="form-label">Kelas</label>
                    <select name="class" id="class" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                        <option value="{{ $class }}" {{ request('class') == $class ? 'selected' : '' }}>{{ $class }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subject" class="form-label">Mata Pelajaran</label>
                    <select name="subject" id="subject" class="form-select">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_range" class="form-label">Periode</label>
                    <select name="date_range" id="date_range" class="form-select">
                        <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="month" {{ request('date_range') == 'month' || !request('date_range') ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="semester" {{ request('date_range') == 'semester' ? 'selected' : '' }}>Semester Ini</option>
                        <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Kustom</option>
                    </select>
                </div>

                <!-- Custom Date Range -->
                <div class="col-12" id="customDateRange" style="display: {{ request('date_range') == 'custom' ? 'block' : 'none' }};">
                    <label class="form-label">Tanggal Mulai - Selesai</label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end mt-3">
                    <button type="button" onclick="resetFilters()" class="btn btn-secondary me-2">
                        <i class="fas fa-redo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Generate Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards - Tetap sama seperti sebelumnya -->

    <!-- Attendance Report Table - Tetap sama seperti sebelumnya -->

    @if($attendances->count() > 0)
    <!-- Charts Section - Tetap sama seperti sebelumnya -->

    <!-- Summary by Class - Tetap sama seperti sebelumnya -->
    @endif
</div>
@endsection