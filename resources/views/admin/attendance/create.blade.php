@extends('layouts.admin')

@section('title', 'Tambah Absensi')
@section('page-title', 'Tambah Data Absensi')
@section('page-subtitle', 'Catat kehadiran siswa secara manual.')

@section('page-actions')
    <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>{{ $errors->count() }} kesalahan:</strong>
        <ul class="mb-0 mt-1 small ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
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

<form action="{{ route('admin.attendance.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        {{-- Main Form --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2"></i>Data Absensi</h6>
                </div>
                <div class="card-body">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="siswa_id" class="form-label fw-semibold">
                                Siswa <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('siswa_id') is-invalid @enderror"
                                    id="siswa_id" name="siswa_id" required>
                                <option value="">— Pilih Siswa —</option>
                                @foreach($students ?? [] as $student)
                                    <option value="{{ $student->id }}" {{ old('siswa_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->user?->name ?? "Siswa #$student->id" }}
                                        @if($student->nis) ({{ $student->nis }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('siswa_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="kelas_id" class="form-label fw-semibold">Kelas</label>
                            <select class="form-select @error('kelas_id') is-invalid @enderror"
                                    id="kelas_id" name="kelas_id">
                                <option value="">— Pilih Kelas —</option>
                                @foreach($kelas ?? [] as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="subject_id" class="form-label fw-semibold">Mata Pelajaran</label>
                            <select class="form-select @error('subject_id') is-invalid @enderror"
                                    id="subject_id" name="subject_id">
                                <option value="">— Pilih Mata Pelajaran —</option>
                                @foreach($subjects ?? [] as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="date" class="form-label fw-semibold">
                                Tanggal <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   id="date" name="date"
                                   value="{{ old('date', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Status <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach(['hadir' => ['success','check-circle'], 'izin' => ['info','info-circle'], 'sakit' => ['warning','heartbeat'], 'alpha' => ['danger','times-circle']] as $val => $cfg)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="status" id="status_{{ $val }}" value="{{ $val }}"
                                               {{ old('status', 'hadir') === $val ? 'checked' : '' }} required>
                                        <label class="form-check-label badge bg-{{ $cfg[0] }} text-white px-3 py-2"
                                               for="status_{{ $val }}" style="cursor:pointer;">
                                            <i class="fas fa-{{ $cfg[1] }} me-1"></i>{{ ucfirst($val) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="note" class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control @error('note') is-invalid @enderror"
                                      id="note" name="note" rows="3"
                                      placeholder="Catatan tambahan (opsional)">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Keterangan Status</h6>
                </div>
                <div class="card-body small">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-success me-2">Hadir</span>
                        <span class="text-muted">Siswa hadir di sekolah</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-info me-2">Izin</span>
                        <span class="text-muted">Tidak hadir dengan izin resmi</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-warning text-dark me-2">Sakit</span>
                        <span class="text-muted">Tidak hadir karena sakit</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-danger me-2">Alpha</span>
                        <span class="text-muted">Tidak hadir tanpa keterangan</span>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Absensi
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@endsection