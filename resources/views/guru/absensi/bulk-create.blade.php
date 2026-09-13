@extends('layouts.guru')

@section('title', 'Absensi Massal per Mata Pelajaran')
@section('page-title', 'Absensi Massal')
@section('page-subtitle', 'Catat kehadiran seluruh siswa dalam satu kelas sekaligus per mata pelajaran.')

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

<div class="row g-4">

    {{-- ── Step 1: Pilih Kelas, Mapel, Tanggal ── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-filter text-primary"></i>
                    <h6 class="mb-0 fw-semibold">Langkah 1 — Pilih Kelas & Mata Pelajaran</h6>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Kelas --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Kelas <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="filterKelas">
                            <option value="">— Pilih Kelas —</option>
                            @foreach($classes ?? [] as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Mata Pelajaran (AJAX) --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Mata Pelajaran <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <select class="form-select" id="filterSubject">
                                <option value="">— Pilih kelas dulu —</option>
                            </select>
                            <div id="subjectSpinner"
                                 class="position-absolute top-50 end-0 translate-middle-y me-3 d-none">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Tanggal <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="filterDate"
                               value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                    </div>

                    {{-- Status default --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Status Default Semua Siswa</label>
                        <select class="form-select form-select-sm" id="defaultStatus">
                            <option value="hadir">Hadir (semua)</option>
                            <option value="alpha">Alpha (semua)</option>
                            <option value="izin">Izin (semua)</option>
                            <option value="sakit">Sakit (semua)</option>
                        </select>
                        <div class="form-text">
                            Ubah status individual di tabel setelah siswa dimuat.
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="button" class="btn btn-primary w-100" id="loadSiswaBtn">
                            <i class="fas fa-users me-1"></i>Muat Daftar Siswa
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-info bg-opacity-10 border-0">
                <h6 class="mb-0 fw-semibold text-info small">
                    <i class="fas fa-info-circle me-2"></i>Cara Penggunaan
                </h6>
            </div>
            <div class="card-body small">
                <ol class="ps-3 mb-0">
                    <li class="mb-1">Pilih <strong>kelas</strong> dan <strong>mata pelajaran</strong>.</li>
                    <li class="mb-1">Tentukan <strong>tanggal</strong> absensi.</li>
                    <li class="mb-1">Klik <strong>Muat Daftar Siswa</strong>.</li>
                    <li class="mb-1">Ubah status tiap siswa bila perlu, lalu klik <strong>Simpan</strong>.</li>
                    <li>Siswa yang sudah diabsen pada mapel + tanggal itu akan <span class="badge bg-warning text-dark">dilewati</span>.</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- ── Step 2: Tabel siswa per mapel ── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm" id="siswaTableCard" style="display:none;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-list-check me-2 text-success"></i>
                        <span id="cardTitle">Daftar Siswa</span>
                    </h6>
                    <small id="cardSubtitle" class="text-muted"></small>
                </div>
                <span class="badge bg-primary" id="siswaCount">0 siswa</span>
            </div>
            <div class="card-body p-0">
                <form id="bulkAbsensiForm" method="POST" action="{{ route('guru.absensi.bulk') }}">
                    @csrf
                    <input type="hidden" name="kelas_id"    id="formKelasId">
                    <input type="hidden" name="subject_id"  id="formSubjectId">
                    <input type="hidden" name="date"        id="formDate">

                    {{-- Toolbar: set semua --}}
                    <div class="d-flex gap-2 px-3 py-2 bg-light border-bottom flex-wrap">
                        <small class="text-muted align-self-center me-1">Set semua:</small>
                        @foreach(['hadir' => 'success', 'izin' => 'info', 'sakit' => 'warning', 'alpha' => 'danger'] as $st => $c)
                        <button type="button" class="btn btn-outline-{{ $c }} btn-sm set-all-btn"
                                data-status="{{ $st }}">
                            {{ ucfirst($st) }}
                        </button>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Siswa</th>
                                    <th class="text-center" style="min-width:260px;">Status</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody id="siswaTableBody">
                                {{-- Diisi via JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-white d-flex gap-2 justify-content-end py-3">
                        <button type="submit" class="btn btn-success fw-semibold" id="submitBulkBtn">
                            <i class="fas fa-save me-1"></i>Simpan Semua Absensi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Placeholder sebelum load --}}
        <div class="card border-0 shadow-sm" id="placeholderCard">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-3x text-muted opacity-25 mb-3 d-block"></i>
                <p class="text-muted">Pilih kelas, mata pelajaran, dan tanggal<br>lalu klik <strong>Muat Daftar Siswa</strong>.</p>
            </div>
        </div>
    </div>

</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const filterKelas   = document.getElementById('filterKelas');
    const filterSubject = document.getElementById('filterSubject');
    const filterDate    = document.getElementById('filterDate');
    const defaultStatus = document.getElementById('defaultStatus');
    const loadBtn       = document.getElementById('loadSiswaBtn');
    const subjectSpinner= document.getElementById('subjectSpinner');

    // ── Load mata pelajaran saat kelas berubah ─────────────────────────
    filterKelas.addEventListener('change', function () {
        const kelasId = this.value;
        filterSubject.innerHTML = '<option value="">— Memuat... —</option>';
        if (!kelasId) {
            filterSubject.innerHTML = '<option value="">— Pilih kelas dulu —</option>';
            return;
        }
        subjectSpinner.classList.remove('d-none');
        fetch(`{{ route('guru.absensi.subjects-by-kelas') }}?kelas_id=${kelasId}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            subjectSpinner.classList.add('d-none');
            const typeLabel = { teori: 'Teori', praktikum: 'Praktikum', campuran: 'Campuran' };
            let html = '<option value="">— Pilih Mata Pelajaran —</option>';
            data.forEach(s => {
                const tipe = s.type ? ` [${typeLabel[s.type] ?? s.type}]` : '';
                html += `<option value="${s.id}">${s.name}${tipe}</option>`;
            });
            filterSubject.innerHTML = html;
        })
        .catch(() => {
            subjectSpinner.classList.add('d-none');
            filterSubject.innerHTML = '<option value="">Gagal memuat</option>';
        });
    });

    // ── Muat daftar siswa ──────────────────────────────────────────────
    loadBtn.addEventListener('click', function () {
        const kelasId   = filterKelas.value;
        const subjectId = filterSubject.value;
        const date      = filterDate.value;
        const defStatus = defaultStatus.value;

        if (!kelasId)   { alert('Pilih kelas terlebih dahulu.'); return; }
        if (!subjectId) { alert('Pilih mata pelajaran terlebih dahulu.'); return; }
        if (!date)      { alert('Isi tanggal terlebih dahulu.'); return; }

        loadBtn.disabled  = true;
        loadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memuat...';

        fetch(`{{ route('guru.absensi.siswa-by-kelas') }}?kelas_id=${kelasId}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(students => {
            loadBtn.disabled  = false;
            loadBtn.innerHTML = '<i class="fas fa-users me-1"></i>Muat Daftar Siswa';

            if (!students.length) {
                alert('Tidak ada siswa di kelas ini.');
                return;
            }

            // Set form fields
            document.getElementById('formKelasId').value   = kelasId;
            document.getElementById('formSubjectId').value = subjectId;
            document.getElementById('formDate').value      = date;

            // Update card header
            const kelasName   = filterKelas.options[filterKelas.selectedIndex].text;
            const subjectName = filterSubject.options[filterSubject.selectedIndex].text;
            document.getElementById('cardTitle').textContent    = kelasName + ' — ' + subjectName;
            document.getElementById('cardSubtitle').textContent = date + ' · ' + students.length + ' siswa';
            document.getElementById('siswaCount').textContent   = students.length + ' siswa';

            // Build rows
            const statusOptions = [
                { val: 'hadir', color: 'success', label: 'Hadir' },
                { val: 'izin',  color: 'info',    label: 'Izin' },
                { val: 'sakit', color: 'warning',  label: 'Sakit' },
                { val: 'alpha', color: 'danger',   label: 'Alpha' },
            ];

            const tbody = document.getElementById('siswaTableBody');
            tbody.innerHTML = '';

            students.forEach((s, i) => {
                const row = document.createElement('tr');
                row.dataset.siswaId = s.id;

                // Status radio buttons
                const statusBtns = statusOptions.map(opt => {
                    const checked = opt.val === defStatus ? 'checked' : '';
                    return `
                        <input type="radio" class="btn-check"
                               name="status[${s.id}]"
                               id="st_${s.id}_${opt.val}"
                               value="${opt.val}" ${checked} required>
                        <label class="btn btn-outline-${opt.color} btn-sm"
                               for="st_${s.id}_${opt.val}">${opt.label}</label>`;
                }).join('');

                row.innerHTML = `
                    <td class="ps-3 text-muted">${i + 1}</td>
                    <td>
                        <div class="fw-semibold">${s.name}</div>
                        ${s.nis ? `<small class="text-muted">${s.nis}</small>` : ''}
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">${statusBtns}</div>
                    </td>
                    <td>
                        <input type="text" name="note[${s.id}]"
                               class="form-control form-control-sm"
                               placeholder="Catatan..." style="min-width:110px;">
                    </td>`;
                tbody.appendChild(row);
            });

            document.getElementById('placeholderCard').style.display  = 'none';
            document.getElementById('siswaTableCard').style.display   = '';
        })
        .catch(err => {
            loadBtn.disabled  = false;
            loadBtn.innerHTML = '<i class="fas fa-users me-1"></i>Muat Daftar Siswa';
            alert('Gagal memuat siswa: ' + err.message);
        });
    });

    // ── Set semua status sekaligus ────────────────────────────────────
    document.querySelectorAll('.set-all-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const status = this.dataset.status;
            document.querySelectorAll(`input[type="radio"][value="${status}"]`)
                .forEach(r => r.checked = true);
        });
    });

    // ── Submit guard ──────────────────────────────────────────────────
    document.getElementById('bulkAbsensiForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBulkBtn');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    });

    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            const btn = document.getElementById('submitBulkBtn');
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Semua Absensi';
        }
    });
});
</script>
@endpush

@endsection
