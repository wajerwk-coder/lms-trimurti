@extends('layouts.admin')

@section('title', 'Edit Absensi')
@section('page-title', 'Edit Data Absensi')
@section('page-subtitle', 'Perbarui catatan kehadiran siswa.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.attendance.show', $attendance) }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-eye me-1"></i>Lihat
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>{{ $errors->count() }} kesalahan:</strong>
        <ul class="mb-0 mt-1 small ps-3">
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

<form action="{{ route('admin.attendance.update', $attendance) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2"></i>Edit Data Absensi</h6>
                </div>
                <div class="card-body">
                    {{-- Info Siswa (readonly) --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">NAMA SISWA</label>
                            <input type="text" class="form-control bg-light" readonly
                                   value="{{ $attendance->siswa?->name ?? '—' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">TANGGAL</label>
                            <input type="text" class="form-control bg-light" readonly
                                   value="{{ \Carbon\Carbon::parse($attendance->date)->translatedFormat('l, d F Y') }}">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kelas_id" class="form-label fw-semibold">Kelas</label>
                            <select class="form-select @error('kelas_id') is-invalid @enderror"
                                    id="kelas_id" name="kelas_id">
                                <option value="">— Pilih Kelas —</option>
                                @foreach($kelas ?? [] as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id', $attendance->kelas_id) == $k->id ? 'selected' : '' }}>
                                        {{ $k->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="subject_id" class="form-label fw-semibold">Mata Pelajaran</label>
                            <select class="form-select @error('subject_id') is-invalid @enderror"
                                    id="subject_id" name="subject_id">
                                <option value="">— Pilih Mapel —</option>
                                @foreach($subjects ?? [] as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $attendance->subject_id) == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Status Kehadiran <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                @foreach([
                                    'hadir'  => ['success', 'check-circle',  'Hadir'],
                                    'izin'   => ['info',    'info-circle',   'Izin'],
                                    'sakit'  => ['warning', 'heartbeat',     'Sakit'],
                                    'alpha'  => ['danger',  'times-circle',  'Alpha'],
                                ] as $val => [$color, $icon, $label])
                                    <div class="col-6 col-md-3">
                                        <input type="radio" class="btn-check" name="status"
                                               id="s_{{ $val }}" value="{{ $val }}"
                                               {{ old('status', $attendance->status) == $val ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-{{ $color }} w-100 text-start"
                                               for="s_{{ $val }}">
                                            <i class="fas fa-{{ $icon }} me-2"></i>{{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="note" class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control @error('note') is-invalid @enderror"
                                      id="note" name="note" rows="3"
                                      placeholder="Catatan (opsional)">{{ old('note', $attendance->note) }}</textarea>
                            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-semibold small">Keterangan Status</h6>
                </div>
                <div class="card-body small">
                    <div class="d-flex mb-2"><span class="badge bg-success me-2">Hadir</span><span class="text-muted">Siswa hadir tepat waktu</span></div>
                    <div class="d-flex mb-2"><span class="badge bg-info me-2">Izin</span><span class="text-muted">Tidak hadir dengan izin</span></div>
                    <div class="d-flex mb-2"><span class="badge bg-warning text-dark me-2">Sakit</span><span class="text-muted">Tidak hadir karena sakit</span></div>
                    <div class="d-flex"><span class="badge bg-danger me-2">Alpha</span><span class="text-muted">Tidak hadir tanpa keterangan</span></div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning fw-semibold text-dark">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </div>
</form>

@endsection