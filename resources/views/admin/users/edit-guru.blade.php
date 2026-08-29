@extends('layouts.admin')

@section('title', 'Edit Guru')
@section('page-title', 'Edit Guru')
@section('page-subtitle', 'Perbarui data akun dan profil guru.')

@section('page-actions')
    <a href="{{ route('admin.users.guru') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@php $profile = $user->guruProfile; @endphp

<form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="editForm">
    @csrf @method('PUT')
    <div class="row g-4">

        {{-- Akun --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-key me-2 text-success"></i>Informasi Akun</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username', $user->username) }}" required>
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror"
                                   value="{{ old('nip', $profile?->nip) }}" required>
                            @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password Baru <span class="text-muted fw-normal">(kosongkan jika tidak diubah)</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min. 8 karakter">
                                <button type="button" class="btn btn-outline-secondary" id="togglePw">
                                    <i class="fas fa-eye" id="pwIcon"></i>
                                </button>
                            </div>
                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Profesional --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-briefcase me-2 text-info"></i>Informasi Profesional</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Mata Pelajaran
                                <span class="badge bg-info ms-1" style="font-size:.65rem;">Pilih lebih dari satu</span>
                            </label>

                            {{-- Search --}}
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" id="subjectSearchEdit" class="form-control border-start-0"
                                       placeholder="Cari mata pelajaran..." autocomplete="off">
                            </div>

                            @error('subject_ids')
                                <div class="text-danger small mb-1">{{ $message }}</div>
                            @enderror

                            <div id="subjectCheckboxListEdit"
                                 class="border rounded-2 p-2"
                                 style="max-height:220px;overflow-y:auto;background:#f8fafc;">
                                @forelse($subjects as $s)
                                <div class="subject-item-edit form-check py-1 px-2 rounded hover-bg"
                                     data-name="{{ strtolower($s->name) }}">
                                    <input class="form-check-input subject-checkbox-edit"
                                           type="checkbox"
                                           name="subject_ids[]"
                                           value="{{ $s->id }}"
                                           id="sedit_{{ $s->id }}"
                                           {{ in_array($s->id, old('subject_ids', $selectedSubjectIds ?? [])) ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="sedit_{{ $s->id }}"
                                           style="cursor:pointer;font-size:.85rem;">
                                        {{ $s->name }}
                                        @if($s->code)
                                            <span class="text-muted">({{ $s->code }})</span>
                                        @endif
                                    </label>
                                </div>
                                @empty
                                <div class="text-muted text-center py-3 small">Belum ada mata pelajaran.</div>
                                @endforelse
                            </div>

                            <div class="mt-1 d-flex align-items-center justify-content-between">
                                <small class="text-muted" id="selectedCountEdit">0 mata pelajaran dipilih</small>
                                <button type="button" class="btn btn-link btn-sm p-0 text-danger"
                                        id="clearAllSubjectsEdit" style="font-size:.75rem;display:none;">
                                    Hapus semua
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pendidikan Terakhir</label>
                            <select name="pendidikan_terakhir" class="form-select">
                                <option value="">Pilih</option>
                                @foreach(['D3','S1','S2','S3'] as $p)
                                    <option value="{{ $p }}" {{ old('pendidikan_terakhir', $profile?->pendidikan_terakhir) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jurusan Pendidikan</label>
                            <input type="text" name="jurusan_pendidikan" class="form-control"
                                   value="{{ old('jurusan_pendidikan', $profile?->jurusan_pendidikan) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tahun Mulai Kerja</label>
                            <input type="number" name="tahun_mulai_kerja" class="form-control"
                                   value="{{ old('tahun_mulai_kerja', $profile?->tahun_mulai_kerja) }}"
                                   min="1970" max="{{ date('Y') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Pribadi</label>
                            <input type="email" name="email_pribadi" class="form-control"
                                   value="{{ old('email_pribadi', $profile?->email_pribadi) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Pribadi --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-user me-2 text-warning"></i>Data Pribadi</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nomor Telepon</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control"
                                   value="{{ old('tempat_lahir', $profile?->tempat_lahir) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control"
                                   value="{{ old('tanggal_lahir', $profile?->tanggal_lahir?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $profile?->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $profile?->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $profile?->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.guru') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
// Subject checkbox search & counter (edit)
document.addEventListener('DOMContentLoaded', function () {
    const searchInput  = document.getElementById('subjectSearchEdit');
    const cbList       = document.getElementById('subjectCheckboxListEdit');
    const countEl      = document.getElementById('selectedCountEdit');
    const clearBtn     = document.getElementById('clearAllSubjectsEdit');

    function updateCount() {
        const n = cbList ? cbList.querySelectorAll('input:checked').length : 0;
        if (countEl) countEl.textContent = n + ' mata pelajaran dipilih';
        if (clearBtn) clearBtn.style.display = n > 0 ? 'inline' : 'none';
    }

    if (searchInput && cbList) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            cbList.querySelectorAll('.subject-item-edit').forEach(function (item) {
                item.style.display = (item.getAttribute('data-name') || '').includes(q) ? '' : 'none';
            });
        });
    }
    if (cbList) { cbList.addEventListener('change', updateCount); updateCount(); }
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            cbList.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
            updateCount();
        });
    }
});

document.getElementById('togglePw').addEventListener('click', function () {
    const pw = document.getElementById('password');
    const ic = document.getElementById('pwIcon');
    const show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    ic.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});
document.getElementById('editForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
});
</script>
@endpush
@endsection
