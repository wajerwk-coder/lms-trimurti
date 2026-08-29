@extends('layouts.admin')

@section('title', 'Edit Jurusan — ' . $jurusan->name)
@section('page-title', 'Edit Jurusan')
@section('page-subtitle', 'Perbarui informasi jurusan: ' . $jurusan->name)

@section('page-actions')
    <a href="{{ route('admin.jurusan.show', $jurusan->id) }}" class="btn btn-outline-info btn-sm me-1">
        <i class="fas fa-eye me-1"></i>Detail
    </a>
    <a href="{{ route('admin.jurusan.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@push('css')
<style>
.preview-banner {
    background: linear-gradient(135deg, #065f46 0%, #059669 50%, #0891b2 100%);
    border-radius: 14px; padding: 1.5rem; position: relative; overflow: hidden;
}
.preview-banner::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:130px; height:130px; border-radius:50%; background:rgba(255,255,255,.06);
}
</style>
@endpush

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <div class="d-flex gap-2">
        <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
        <div>
            <strong>{{ $errors->count() }} kesalahan:</strong>
            <ul class="mb-0 mt-1 ps-3 small">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.jurusan.update', $jurusan->id) }}" method="POST" id="jurusanForm" novalidate>
@csrf
@method('PUT')

<div class="row g-4">

    {{-- ═══ KIRI: Form ═══ --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-2 p-2 bg-warning bg-opacity-10 lh-1">
                        <i class="fas fa-edit text-warning"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Informasi Jurusan</h6>
                        <small class="text-muted">Nama, kode, status, dan deskripsi</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Nama --}}
                    <div class="col-12">
                        <label class="form-label small fw-semibold">
                            Nama Jurusan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-graduation-cap text-muted"></i>
                            </span>
                            <input type="text" name="name" id="namaInput"
                                   class="form-control border-start-0 @error('name') is-invalid @enderror"
                                   value="{{ old('name', $jurusan->name) }}"
                                   placeholder="Contoh: Keperawatan"
                                   autocomplete="off" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Kode --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">
                            Kode <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-hashtag text-muted"></i>
                            </span>
                            <input type="text" name="code" id="kodeInput"
                                   class="form-control border-start-0 @error('code') is-invalid @enderror"
                                   value="{{ old('code', $jurusan->code) }}"
                                   placeholder="KEP"
                                   maxlength="20"
                                   style="text-transform:uppercase;"
                                   autocomplete="off" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">3–5 karakter, huruf besar.</div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="w-100">
                            <label class="form-label small fw-semibold d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox"
                                       id="isActiveCheck" name="is_active" value="1"
                                       {{ old('is_active', $jurusan->is_active) ? 'checked' : '' }}
                                       style="width:2.5em;height:1.25em;">
                                <label class="form-check-label ms-2 fw-semibold" for="isActiveCheck"
                                       id="activeLabel">{{ $jurusan->is_active ? 'Aktif' : 'Nonaktif' }}</label>
                            </div>
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="col-12">
                        <label class="form-label small fw-semibold">
                            Deskripsi
                            <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea name="description" id="deskripsiInput"
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Deskripsi singkat program keahlian ini...">{{ old('description', $jurusan->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>
        </div>
    </div>{{-- /col-lg-7 --}}

    {{-- ═══ KANAN: Preview & Aksi ═══ --}}
    <div class="col-lg-5">

        {{-- Live Preview --}}
        <div class="preview-banner mb-4">
            <div class="position-relative" style="z-index:1;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 bg-white bg-opacity-20 d-flex align-items-center
                                justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;">
                        <i class="fas fa-graduation-cap text-white fa-lg"></i>
                    </div>
                    <div class="overflow-hidden flex-grow-1">
                        <div id="previewNama" class="fw-bold text-white fs-6 text-truncate">{{ $jurusan->name }}</div>
                        <div id="previewDesc" class="text-white opacity-75 small text-truncate">{{ $jurusan->description ?: '—' }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span id="previewKode" class="badge rounded-pill" style="background:rgba(255,255,255,.2);">
                        Kode: {{ $jurusan->code }}
                    </span>
                    <span id="previewStatus" class="badge rounded-pill" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-circle me-1" style="font-size:.55rem;"></i>
                        {{ $jurusan->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-chart-bar me-2 text-success"></i>Statistik Jurusan
                </h6>
            </div>
            <div class="card-body py-3">
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="h4 fw-bold text-primary mb-0">{{ $jurusan->kelas_count ?? $jurusan->kelas->count() }}</div>
                        <small class="text-muted">Total Kelas</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 fw-bold text-success mb-0">{{ $jurusan->siswa_count ?? 0 }}</div>
                        <small class="text-muted">Total Siswa</small>
                    </div>
                </div>
                @if($jurusan->created_at)
                <hr class="my-2">
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    Dibuat: {{ $jurusan->created_at->format('d M Y') }}
                    · Diperbarui: {{ $jurusan->updated_at->format('d M Y') }}
                </small>
                @endif
            </div>
        </div>

        {{-- Tombol --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column gap-2">
                <button type="submit" class="btn btn-warning fw-semibold text-dark" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
                <a href="{{ route('admin.jurusan.show', $jurusan->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>

    </div>{{-- /col-lg-5 --}}

</div>{{-- /row --}}
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const namaEl    = document.getElementById('namaInput');
    const kodeEl    = document.getElementById('kodeInput');
    const descEl    = document.getElementById('deskripsiInput');
    const activeEl  = document.getElementById('isActiveCheck');
    const activeLbl = document.getElementById('activeLabel');

    const pNama   = document.getElementById('previewNama');
    const pDesc   = document.getElementById('previewDesc');
    const pKode   = document.getElementById('previewKode');
    const pStatus = document.getElementById('previewStatus');

    function updatePreview() {
        const nama   = namaEl.value.trim();
        const kode   = kodeEl.value.trim().toUpperCase();
        const desc   = descEl.value.trim();
        const active = activeEl.checked;

        pNama.textContent  = nama  || 'Nama Jurusan';
        pDesc.textContent  = desc  || '—';
        pKode.textContent  = kode  ? 'Kode: ' + kode : 'Kode —';
        pStatus.innerHTML  = '<i class="fas fa-circle me-1" style="font-size:.55rem;"></i>' +
                             (active ? 'Aktif' : 'Nonaktif');
        activeLbl.textContent = active ? 'Aktif' : 'Nonaktif';
    }

    namaEl.addEventListener('input', updatePreview);
    kodeEl.addEventListener('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        updatePreview();
    });
    descEl.addEventListener('input', updatePreview);
    activeEl.addEventListener('change', updatePreview);

    document.getElementById('jurusanForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Perubahan';
    });

});
</script>
@endpush

@endsection