@extends('layouts.guru')

@section('title', 'Absensi Massal')
@section('page-title', 'Absensi Massal')
@section('page-subtitle', 'Catat kehadiran seluruh siswa dalam satu kelas sekaligus.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('guru.absensi.create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-user me-1"></i>Absensi Satu Siswa
        </a>
        <a href="{{ route('guru.absensi.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
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

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-users me-2"></i>Absensi Satu Kelas
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('guru.absensi.bulk') }}" method="POST" id="bulkForm" novalidate>
                    @csrf
                    <div class="row g-3">

                        {{-- Kelas --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Kelas <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('class') is-invalid @enderror"
                                    name="class" required>
                                <option value="">— Pilih Kelas —</option>
                                @foreach($classes ?? [] as $id => $name)
                                    <option value="{{ $id }}" {{ old('class') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Mata Pelajaran --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mata Pelajaran</label>
                            <select class="form-select @error('subject_id') is-invalid @enderror"
                                    name="subject_id">
                                <option value="">— Pilih Mapel —</option>
                                @foreach($subjects ?? [] as $subject)
                                    <option value="{{ $subject->id }}"
                                        {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Tanggal --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Tanggal <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   name="date"
                                   value="{{ old('date', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Status default semua siswa --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Status Default <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    name="status" required>
                                <option value="hadir" {{ old('status','hadir')=='hadir' ? 'selected':'' }}>
                                    ✅ Hadir (semua siswa)
                                </option>
                                <option value="alpha" {{ old('status')=='alpha' ? 'selected':'' }}>
                                    ❌ Alpha (semua siswa)
                                </option>
                                <option value="izin"  {{ old('status')=='izin'  ? 'selected':'' }}>
                                    ℹ️ Izin (semua siswa)
                                </option>
                                <option value="sakit" {{ old('status')=='sakit' ? 'selected':'' }}>
                                    🤒 Sakit (semua siswa)
                                </option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Catatan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control"
                                      name="note" rows="2"
                                      placeholder="Catatan opsional untuk semua siswa">{{ old('note') }}</textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Simpan Absensi Massal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        {{-- Panduan --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info bg-opacity-10 border-0">
                <h6 class="mb-0 fw-semibold text-info">
                    <i class="fas fa-info-circle me-2"></i>Cara Penggunaan
                </h6>
            </div>
            <div class="card-body small">
                <ol class="ps-3 mb-0">
                    <li class="mb-2">Pilih <strong>kelas</strong> yang ingin diabsen.</li>
                    <li class="mb-2">Tentukan <strong>tanggal</strong> absensi (maks. hari ini).</li>
                    <li class="mb-2">Pilih <strong>status default</strong> — biasanya "Hadir" lalu ubah yang tidak hadir secara manual.</li>
                    <li class="mb-2">Klik <strong>Simpan Absensi Massal</strong>.</li>
                    <li>Siswa yang sudah diabsen pada tanggal itu akan <strong>dilewati</strong> (tidak duplikat).</li>
                </ol>
            </div>
        </div>

        {{-- Link ke absensi satu per satu --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Butuh lebih spesifik?</h6>
                <p class="text-muted small mb-3">
                    Gunakan absensi satu siswa untuk mencatat catatan berbeda per siswa,
                    atau untuk mengoreksi absensi yang sudah ada.
                </p>
                <a href="{{ route('guru.absensi.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-user me-1"></i>Absensi Satu Siswa
                </a>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('bulkForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Absensi Massal';
        }
    });
});
</script>
@endpush

@endsection
