@extends('layouts.guru')

@section('title', 'Edit Absensi')
@section('page-title', 'Edit Absensi')
@section('page-subtitle', 'Perbarui data kehadiran siswa.')

@section('page-actions')
    <a href="{{ route('guru.absensi.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

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

<form action="{{ route('guru.absensi.update', $absensi) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Info siswa (readonly) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-user me-2 text-primary"></i>Info Siswa</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Nama Siswa</label>
                            <input type="text" class="form-control bg-light" readonly
                                   value="{{ $absensi->siswa?->name ?? '—' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Tanggal Absensi</label>
                            <input type="text" class="form-control bg-light" readonly
                                   value="{{ \Carbon\Carbon::parse($absensi->date)->format('d/m/Y') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Edit --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Data Absensi</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kelas</label>
                            <select class="form-select" name="kelas_id">
                                <option value="">— Pilih Kelas —</option>
                                @foreach($kelas ?? [] as $id => $name)
                                    <option value="{{ $id }}" {{ $absensi->kelas_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mata Pelajaran</label>
                            <select class="form-select" name="subject_id">
                                <option value="">— Pilih Mapel —</option>
                                @foreach($subjects ?? [] as $subject)
                                    <option value="{{ $subject->id }}" {{ $absensi->subject_id == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Status Kehadiran <span class="text-danger">*</span>
                            </label>
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
                                               {{ old('status', $absensi->status) == $val ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-{{ $color }} w-100 text-start"
                                               for="s_{{ $val }}">
                                            <i class="fas fa-{{ $icon }} me-2"></i>{{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control @error('note') is-invalid @enderror"
                                      name="note" rows="3"
                                      placeholder="Catatan (opsional)">{{ old('note', $absensi->note) }}</textarea>
                            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-2 mt-lg-4 pt-lg-3">
                <button type="submit" class="btn btn-warning text-dark fw-semibold">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
                <a href="{{ route('guru.absensi.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
                <hr class="my-1">
                <button type="button" class="btn btn-outline-danger btn-sm"
                        onclick="if(confirm('Hapus data ini?')) document.getElementById('delForm').submit()">
                    <i class="fas fa-trash me-1"></i>Hapus Data Ini
                </button>
                <form id="delForm" action="{{ route('guru.absensi.destroy', $absensi) }}" method="POST">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</form>

@endsection