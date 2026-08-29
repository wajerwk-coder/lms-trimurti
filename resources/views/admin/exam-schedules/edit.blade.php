@extends('layouts.admin')

@section('title', 'Edit Jadwal')
@section('page-title', 'Edit Jadwal')
@section('page-subtitle', 'Perbarui informasi jadwal')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.exam-schedules.index') }}">Jadwal</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Jadwal</li>
@endsection

@section('content')
<div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-edit me-2"></i>Edit Jadwal
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>{{ $errors->count() }} kesalahan:</strong>
                            <ul class="mb-0 mt-1 ps-3 small">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.exam-schedules.update', $examSchedule) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title', $examSchedule->title) }}" placeholder="Contoh: UTS Matematika Semester Ganjil" required>
                                @error('title')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" placeholder="Deskripsi singkat...">{{ old('description', $examSchedule->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                <select name="exam_type" class="form-select @error('exam_type') is-invalid @enderror" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="uts" {{ old('exam_type', $examSchedule->exam_type) == 'uts' ? 'selected' : '' }}>UTS (Ujian Tengah Semester)</option>
                                    <option value="uas" {{ old('exam_type', $examSchedule->exam_type) == 'uas' ? 'selected' : '' }}>UAS (Ujian Akhir Semester)</option>
                                    <option value="quiz" {{ old('exam_type', $examSchedule->exam_type) == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                    <option value="praktikum" {{ old('exam_type', $examSchedule->exam_type) == 'praktikum' ? 'selected' : '' }}>Praktikum</option>
                                    <option value="lainnya" {{ old('exam_type', $examSchedule->exam_type) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('exam_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                    <option value="">Pilih Mata Pelajaran</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id', $examSchedule->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Kelas</label>
                                <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id', $examSchedule->kelas_id) == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Kosongkan jika ujian berlaku untuk semua kelas</small>
                                @error('kelas_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Lokasi</label>
                                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" 
                                       value="{{ old('location', $examSchedule->location) }}" placeholder="Contoh: Ruang 201, Lab Komputer">
                                @error('location')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                                       value="{{ old('start_time', $examSchedule->start_time->format('Y-m-d\TH:i')) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                                       value="{{ old('end_time', $examSchedule->end_time->format('Y-m-d\TH:i')) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                       value="{{ old('duration_minutes', $examSchedule->duration_minutes) }}" min="1" placeholder="60" required>
                                @error('duration_minutes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Status Publikasi</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_published" value="1" 
                                           {{ old('is_published', $examSchedule->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Publikasikan jadwal
                                    </label>
                                </div>
                                <small class="text-muted">
                                    @if($examSchedule->is_published)
                                        <span class="text-success">Jadwal telah dipublikasikan</span>
                                    @else
                                        <span class="text-warning">Jadwal belum dipublikasikan</span>
                                    @endif
                                </small>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-2"></i>Perbarui
                                    </button>
                                    <a href="{{ route('admin.exam-schedules.show', $examSchedule) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Batal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Warning Card -->
            @if($examSchedule->is_published)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Peringatan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning d-flex align-items-start">
                        <i class="fas fa-info-circle me-2 mt-1"></i>
                        <div>
                            <strong>Jadwal Telah Dipublikasikan</strong>
                            <p class="mb-0 small">Jadwal ini telah dikirim ke guru dan siswa. Perubahan akan langsung terlihat, tetapi notifikasi baru hanya akan dikirim jika status publikasi berubah dari tidak dipublikasi menjadi dipublikasi.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Current Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Info Saat Ini
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted">Status:</small>
                            <div>
                                <span class="badge bg-{{ $examSchedule->status_color }}">
                                    {{ $examSchedule->status }}
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Dibuat oleh:</small>
                            <div class="small">{{ $examSchedule->creator?->name ?? '—' }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Dibuat pada:</small>
                            <div class="small">{{ $examSchedule->created_at->format('d M Y H:i') }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Diperbarui pada:</small>
                            <div class="small">{{ $examSchedule->updated_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tips Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Tips Edit
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li>Periksa kembali semua field sebelum menyimpan</li>
                        <li>Waktu selesai harus setelah waktu mulai</li>
                        <li>Jika mengubah kelas, notifikasi akan dikirim ke kelas baru</li>
                        <li>Perubahan langsung terlihat setelah disimpan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection