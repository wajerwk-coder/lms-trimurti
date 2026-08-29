@extends('layouts.guru')

@section('title', 'Tambah Absensi')
@section('page-title', 'Tambah Absensi')
@section('page-subtitle', 'Catat kehadiran satu siswa.')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('guru.absensi.bulk-create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-users me-1"></i>Absensi Massal
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

<form action="{{ route('guru.absensi.store') }}" method="POST" id="absensiForm" novalidate>
    @csrf

    <div class="row g-4">

        {{-- ═══ KIRI: Form Utama ═══ --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-calendar-check me-2"></i>Data Absensi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Kelas (AJAX trigger) --}}
                        <div class="col-md-6">
                            <label for="kelas_id" class="form-label fw-semibold">
                                Kelas <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="kelas_id" name="kelas_id" required>
                                <option value="">— Pilih Kelas —</option>
                                @foreach($classes as $k)
                                    <option value="{{ $k->id }}"
                                        {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Siswa (diisi via AJAX) --}}
                        <div class="col-md-6">
                            <label for="siswa_id" class="form-label fw-semibold">
                                Siswa <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative">
                                <select class="form-select @error('siswa_id') is-invalid @enderror"
                                        id="siswa_id" name="siswa_id" required>
                                    <option value="">— Pilih kelas dulu —</option>
                                </select>
                                {{-- Spinner overlay saat loading --}}
                                <div id="siswaSpinner"
                                     class="position-absolute top-50 end-0 translate-middle-y me-3 d-none">
                                    <span class="spinner-border spinner-border-sm text-primary"></span>
                                </div>
                            </div>
                            <div id="siswaHint" class="form-text text-muted">
                                Pilih kelas untuk memuat daftar siswa.
                            </div>
                            @error('siswa_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Mata Pelajaran --}}
                        <div class="col-md-6">
                            <label for="subject_id" class="form-label fw-semibold">
                                Mata Pelajaran
                            </label>
                            <select class="form-select @error('subject_id') is-invalid @enderror"
                                    id="subject_id" name="subject_id">
                                <option value="">— Pilih Mapel —</option>
                                @foreach($subjects as $subject)
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
                            <label for="date" class="form-label fw-semibold">
                                Tanggal <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   id="date" name="date"
                                   value="{{ old('date', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Status Kehadiran --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Status Kehadiran <span class="text-danger">*</span>
                            </label>
                            <div class="row g-2">
                                @foreach([
                                    'hadir' => ['success', 'check-circle',  'Hadir'],
                                    'izin'  => ['info',    'info-circle',   'Izin'],
                                    'sakit' => ['warning', 'heartbeat',     'Sakit'],
                                    'alpha' => ['danger',  'times-circle',  'Alpha'],
                                ] as $val => [$color, $icon, $label])
                                    <div class="col-6 col-sm-3">
                                        <input type="radio" class="btn-check"
                                               name="status" id="s_{{ $val }}" value="{{ $val }}"
                                               {{ old('status', 'hadir') === $val ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-{{ $color }} w-100"
                                               for="s_{{ $val }}">
                                            <i class="fas fa-{{ $icon }} me-1"></i>{{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Catatan --}}
                        <div class="col-12">
                            <label for="note" class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control @error('note') is-invalid @enderror"
                                      id="note" name="note" rows="2"
                                      placeholder="Keterangan tambahan (opsional)">{{ old('note') }}</textarea>
                            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>{{-- /row --}}
                </div>{{-- /card-body --}}
            </div>{{-- /card --}}
        </div>{{-- /col-lg-8 --}}

        {{-- ═══ KANAN: Info & Tombol ═══ --}}
        <div class="col-lg-4">

            {{-- Keterangan status --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-info bg-opacity-10 border-0 py-2">
                    <h6 class="mb-0 fw-semibold text-info small">
                        <i class="fas fa-info-circle me-2"></i>Keterangan Status
                    </h6>
                </div>
                <div class="card-body py-3 small">
                    @foreach([
                        ['success', 'Hadir',  'Siswa hadir di kelas'],
                        ['info',    'Izin',   'Tidak hadir, ada izin resmi'],
                        ['warning', 'Sakit',  'Tidak hadir karena sakit'],
                        ['danger',  'Alpha',  'Tidak hadir tanpa keterangan'],
                    ] as [$color, $label, $desc])
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <span class="badge bg-{{ $color }} mt-1 flex-shrink-0" style="min-width:48px;text-align:center;">
                                {{ $label }}
                            </span>
                            <span class="text-muted">{{ $desc }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Info duplikasi --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3 small text-muted">
                    <i class="fas fa-shield-alt text-success me-2"></i>
                    Sistem akan menolak jika absensi siswa pada tanggal yang sama sudah dicatat sebelumnya.
                </div>
            </div>

            {{-- Tombol --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-semibold" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Simpan Absensi
                </button>
                <a href="{{ route('guru.absensi.bulk-create') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-users me-1"></i>Absensi Massal (satu kelas)
                </a>
                <a href="{{ route('guru.absensi.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>

        </div>{{-- /col-lg-4 --}}
    </div>{{-- /row --}}
</form>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const kelasSelect  = document.getElementById('kelas_id');
    const siswaSelect  = document.getElementById('siswa_id');
    const siswaSpinner = document.getElementById('siswaSpinner');
    const siswaHint    = document.getElementById('siswaHint');
    const submitBtn    = document.getElementById('submitBtn');
    const csrfToken    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Load siswa via AJAX saat kelas berubah ─────────────────────────
    kelasSelect.addEventListener('change', function () {
        const kelasId = this.value;

        // Reset dropdown siswa
        siswaSelect.innerHTML = '<option value="">— Memuat siswa... —</option>';
        // (disabled removed — field must always submit)
        siswaHint.textContent = '';

        if (!kelasId) {
            siswaSelect.innerHTML = '<option value="">— Pilih kelas dulu —</option>';
            siswaHint.textContent = 'Pilih kelas untuk memuat daftar siswa.';
            return;
        }

        siswaSpinner.classList.remove('d-none');

        fetch(`{{ route('guru.absensi.siswa-by-kelas') }}?kelas_id=${kelasId}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Server error ' + res.status);
            return res.json();
        })
        .then(data => {
            siswaSpinner.classList.add('d-none');

            if (!data.length) {
                siswaSelect.innerHTML = '<option value="">Tidak ada siswa di kelas ini</option>';
                siswaHint.textContent = 'Kelas ini belum memiliki siswa.';
                siswaHint.className = 'form-text text-warning';
                return;
            }

            const oldVal = '{{ old('siswa_id') }}';
            let html = '<option value="">— Pilih Siswa —</option>';
            data.forEach(s => {
                const label = s.name + (s.nis ? ` (${s.nis})` : '');
                const sel   = oldVal && String(oldVal) === String(s.id) ? ' selected' : '';
                html += `<option value="${s.id}"${sel}>${label}</option>`;
            });
            siswaSelect.innerHTML = html;
            siswaSelect.disabled  = false;
            siswaHint.textContent = data.length + ' siswa ditemukan.';
            siswaHint.className   = 'form-text text-success';
        })
        .catch(err => {
            siswaSpinner.classList.add('d-none');
            siswaSelect.innerHTML = '<option value="">Gagal memuat siswa</option>';
            siswaHint.textContent = 'Gagal memuat daftar siswa: ' + err.message;
            siswaHint.className   = 'form-text text-danger';
        });
    });

    // ── Jika ada old('kelas_id') setelah validasi gagal, trigger AJAX ──
    const oldKelas = kelasSelect.value;
    if (oldKelas) {
        kelasSelect.dispatchEvent(new Event('change'));
    }

    // ── Submit spinner ─────────────────────────────────────────────────
    document.getElementById('absensiForm').addEventListener('submit', function (e) {
        // Cegah submit jika siswa belum dipilih
        if (!siswaSelect.value) {
            e.preventDefault();
            siswaSelect.classList.add('is-invalid');
            siswaHint.textContent = 'Siswa wajib dipilih.';
            siswaHint.className   = 'form-text text-danger';
            return;
        }
        submitBtn.disabled   = true;
        submitBtn.innerHTML  = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });

    // Restore on bfcache
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            submitBtn.disabled  = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Absensi';
        }
    });

});
</script>
@endpush

@endsection
