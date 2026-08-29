@extends('layouts.admin')

@section('title', 'Buat Jadwal')
@section('page-title', 'Buat Jadwal')
@section('page-subtitle', 'Buat jadwal baru dan kirim notifikasi ke guru dan siswa')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.exam-schedules.index') }}">Jadwal</a></li>
    <li class="breadcrumb-item active" aria-current="page">Buat Jadwal</li>
@endsection

@section('content')
<div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-plus me-2"></i>Informasi Jadwal
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Terdapat kesalahan pada input:
                            </h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('admin.exam-schedules.store') }}">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}" placeholder="Masukkan judul" required>
                                @error('title')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" placeholder="Deskripsi singkat...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                <select name="exam_type" class="form-select @error('exam_type') is-invalid @enderror" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="uts" {{ old('exam_type') == 'uts' ? 'selected' : '' }}>UTS (Ujian Tengah Semester)</option>
                                    <option value="uas" {{ old('exam_type') == 'uas' ? 'selected' : '' }}>UAS (Ujian Akhir Semester)</option>
                                    <option value="quiz" {{ old('exam_type') == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                    <option value="praktikum" {{ old('exam_type') == 'praktikum' ? 'selected' : '' }}>Praktikum</option>
                                    <option value="lainnya" {{ old('exam_type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
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
                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
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
                                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
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
                                       value="{{ old('location') }}" placeholder="Contoh: Ruang 201, Lab Komputer">
                                @error('location')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                                       value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                                       value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                       value="{{ old('duration_minutes', 60) }}" min="1" placeholder="60" required>
                                @error('duration_minutes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Publikasikan Sekarang?</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_published" value="1" 
                                           {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Ya, kirim notifikasi ke guru dan siswa
                                    </label>
                                </div>
                                <small class="text-muted">Jika dicentang, notifikasi akan dikirim ke semua guru dan siswa terkait</small>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan
                                    </button>
                                    <a href="{{ route('admin.exam-schedules.index') }}" class="btn btn-secondary">
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
            <!-- Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-start">
                        <i class="fas fa-bell me-2 mt-1"></i>
                        <div>
                            <strong>Notifikasi Otomatis</strong>
                            <p class="mb-0 small">Saat jadwal dipublikasikan, notifikasi akan otomatis dikirim ke:</p>
                            <ul class="mb-0 small">
                                <li>Semua siswa di kelas terkait</li>
                                <li>Semua guru pengajar di kelas terkait</li>
                                <li>Jika tidak ada kelas, notifikasi ke semua siswa dan guru</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-success d-flex align-items-start">
                        <i class="fas fa-check-circle me-2 mt-1"></i>
                        <div>
                            <strong>Konten Notifikasi</strong>
                            <p class="mb-0 small">Notifikasi akan berisi:</p>
                            <ul class="mb-0 small">
                                <li>Judul dan tipe</li>
                                <li>Mata pelajaran</li>
                                <li>Waktu dan lokasi</li>
                                <li>Link ke detail jadwal</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tips Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li>Buat jadwal minimal 1 hari sebelum</li>
                        <li>Periksa kembali waktu mulai dan selesai</li>
                        <li>Isi lokasi jika dilakukan di ruangan tertentu</li>
                        <li>Publikasikan jadwal setelah semua data benar</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection