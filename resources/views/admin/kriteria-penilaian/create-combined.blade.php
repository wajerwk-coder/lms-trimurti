@extends('layouts.admin')

@section('title', 'Tambah Kriteria Penilaian (Gabungan)')

@section('content')
<div>
    <div class="row">
        <div class="col-12 mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.kriteria-penilaian.index') }}">Kriteria Penilaian</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah (Gabungan)</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-layer-group me-2"></i>
                        <span>Tambah Kriteria Penilaian - Sekaligus untuk 4 Kategori</span>
                    </div>
                    <a href="{{ route('admin.kriteria-penilaian.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.kriteria-penilaian.store-combined') }}" method="POST" id="combinedForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mata Praktik <span class="text-danger">*</span></label>
                                <select name="mata_praktik" class="form-control @error('mata_praktik') is-invalid @enderror" required>
                                    <option value="">Pilih Mata Pelajaran</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->name }}" {{ old('mata_praktik')==$subject->name ? 'selected' : '' }}>
                                            {{ $subject->name }} ({{ $subject->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('mata_praktik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                                <select name="tingkat_kelas" class="form-control @error('tingkat_kelas') is-invalid @enderror" required>
                                    <option value="">Pilih Tingkat</option>
                                    @foreach($tingkatKelasList as $val => $label)
                                        <option value="{{ $val }}" {{ old('tingkat_kelas')===$val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('tingkat_kelas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" {{ old('is_active', 1)==1 ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('is_active')==='0' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <span>Pastikan total bobot dari keempat kategori berjumlah 100%.</span>
                            <span class="badge bg-dark">Total Bobot Saat Ini: <span id="totalBobot">0</span>%</span>
                        </div>

                        @php
                            $defaults = [
                                'persiapan'  => ['label' => 'Persiapan',          'color' => 'info',    'default_weight' => 20],
                                'pelaksanaan'=> ['label' => 'Pelaksanaan',         'color' => 'primary', 'default_weight' => 40],
                                'hasil'      => ['label' => 'Hasil',               'color' => 'success', 'default_weight' => 25],
                                'sikap'      => ['label' => 'Sikap Profesional',   'color' => 'warning', 'default_weight' => 15],
                            ];
                        @endphp

                        @foreach($defaults as $key => $meta)
                        <div class="card mb-3">
                            <div class="card-header bg-{{ $meta['color'] }} text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Kategori: {{ $meta['label'] }}</h6>
                                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#section-{{ $key }}" aria-expanded="true">Tampilkan/Sembunyikan</button>
                            </div>
                            <div id="section-{{ $key }}" class="collapse show">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                                            <input type="text" name="categories[{{ $key }}][name]"
                                                value="{{ old("categories.$key.name") }}"
                                                class="form-control"
                                                placeholder="Nama kriteria {{ strtolower($meta['label']) }}" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Bobot (%) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" step="1" min="1" max="100"
                                                    name="categories[{{ $key }}][weight]"
                                                    value="{{ old("categories.$key.weight", $meta['default_weight']) }}"
                                                    class="form-control bobot-input" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Checklist (jumlah)</label>
                                            <input type="number" min="1" value="3" class="form-control checklist-init-count" data-bs-target="{{ $key }}">
                                            <small class="text-muted">Klik "+ Item" untuk menambah baris baru</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea name="categories[{{ $key }}][description]" rows="2" class="form-control" placeholder="Deskripsi singkat"></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label mb-0">SOP Checklist <span class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-sm btn-outline-{{ $meta['color'] }} add-checklist" data-bs-target="{{ $key }}">
                                            <i class="fas fa-plus me-1"></i>+ Item
                                        </button>
                                    </div>
                                    <div id="checklist-{{ $key }}" class="mt-2 checklist-container">
                                        @php
                                            $oldChecklist = old("categories.$key.sop_checklist", ["", "", ""]);
                                        @endphp
                                        @foreach($oldChecklist as $idx => $val)
                                            <div class="input-group mb-2 checklist-item">
                                                <span class="input-group-text">{{ $idx + 1 }}</span>
                                                <input type="text" name="categories[{{ $key }}][sop_checklist][]" value="{{ $val }}" class="form-control" placeholder="Item SOP" required>
                                                <button type="button" class="btn btn-outline-danger remove-checklist"><i class="fas fa-trash"></i></button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <a href="{{ route('admin.kriteria-penilaian.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Simpan Semua
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const PRECISION = 3; // tolerance when comparing floats

    function renumber(container) {
        container.querySelectorAll('.checklist-item').forEach((row, idx) => {
            const badge = row.querySelector('.input-group-text');
            if (badge) badge.textContent = (idx + 1);
        });
    }

    document.querySelectorAll('.add-checklist').forEach(btn => {
        btn.addEventListener('click', function() {
            const key = this.getAttribute('data-target');
            const container = document.getElementById('checklist-' + key);
            const row = document.createElement('div');
            row.className = 'input-group mb-2 checklist-item';
            row.innerHTML = `
                <span class="input-group-text"></span>
                <input type="text" name="categories[${key}][sop_checklist][]" class="form-control" placeholder="Item SOP" required>
                <button type="button" class="btn btn-outline-danger remove-checklist"><i class="fas fa-trash"></i></button>
            `;
            container.appendChild(row);
            renumber(container);
        });
    });

    document.querySelectorAll('.checklist-container').forEach(container => {
        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-checklist')) {
                const row = e.target.closest('.checklist-item');
                row.remove();
                if (this.children.length === 0) {
                    // keep at least one row
                    const addBtn = document.querySelector(`.add-checklist[data-bs-target="${this.id.replace('checklist-','')}"]`);
                    addBtn?.click();
                }
                renumber(this);
            }
        });
    });

    // Initialize counts from numeric inputs
    document.querySelectorAll('.checklist-init-count').forEach(input => {
        input.addEventListener('change', function() {
            const key = this.getAttribute('data-target');
            const desired = Math.max(1, parseInt(this.value || '1', 10));
            const container = document.getElementById('checklist-' + key);
            const current = container.querySelectorAll('.checklist-item').length;
            if (desired > current) {
                for (let i = 0; i < (desired - current); i++) {
                    const btn = document.querySelector(`.add-checklist[data-bs-target="${key}"]`);
                    btn?.click();
                }
            } else if (desired < current) {
                const items = Array.from(container.querySelectorAll('.checklist-item'));
                for (let i = 0; i < (current - desired); i++) {
                    items.pop()?.remove();
                }
                renumber(container);
            }
        });
    });

    // Live total bobot (tanpa MutationObserver untuk mencegah potensi freeze)
    const totalBobotEl = document.getElementById('totalBobot');
    const submitBtn = document.getElementById('submitBtn');

    function getBobotInputs() {
        return Array.from(document.querySelectorAll('.bobot-input'));
    }

    function updateTotalBobot() {
        const inputs = getBobotInputs();
        let sum = 0;
        for (const inp of inputs) {
            const val = parseInt(inp.value, 10);
            if (!isNaN(val)) sum += val;
        }
        totalBobotEl.textContent = sum;

        const isValid = sum === 100;
        submitBtn.disabled = !isValid;
        submitBtn.title = isValid ? '' : 'Total bobot harus 100%';
        totalBobotEl.parentElement.className = isValid ? 'badge bg-success' : 'badge bg-danger';
    }

    // Pasang listener pada input bobot statis
    getBobotInputs().forEach(inp => {
        inp.addEventListener('input', updateTotalBobot);
        inp.addEventListener('change', updateTotalBobot);
    });

    // Hitung awal saat halaman dimuat
    updateTotalBobot();
});
</script>
@endpush
