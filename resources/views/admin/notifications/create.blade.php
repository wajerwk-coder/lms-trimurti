@extends('layouts.admin')

@section('title', 'Kirim Notifikasi')
@section('page-title', 'Kirim Notifikasi')
@section('page-subtitle', 'Buat dan kirim notifikasi ke guru atau siswa')

@section('content')
<div class="container-fluid px-0">

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="fas fa-paper-plane text-primary me-2"></i>Kirim Notifikasi Baru
                    </h5>
                    <p class="text-muted mb-0 small">Isi formulir di bawah untuk mengirim notifikasi ke guru atau siswa</p>
                </div>
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.notifications.store') }}" id="notifForm">
        @csrf

        <div class="row g-4">

            {{-- Kiri: Form utama --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold"><i class="fas fa-edit me-2 text-primary"></i>Isi Notifikasi</h6>
                    </div>
                    <div class="card-body">

                        {{-- Judul --}}
                        <div class="mb-3">
                            <label for="judul" class="form-label fw-semibold">
                                Judul Notifikasi <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="judul" name="judul"
                                   class="form-control @error('judul') is-invalid @enderror"
                                   value="{{ old('judul') }}"
                                   placeholder="Contoh: Pengumuman Libur Nasional"
                                   maxlength="255" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Pesan --}}
                        <div class="mb-3">
                            <label for="pesan" class="form-label fw-semibold">
                                Isi Pesan <span class="text-danger">*</span>
                            </label>
                            <textarea id="pesan" name="pesan"
                                      class="form-control @error('pesan') is-invalid @enderror"
                                      rows="5"
                                      placeholder="Tulis isi notifikasi yang jelas dan informatif..."
                                      required>{{ old('pesan') }}</textarea>
                            @error('pesan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Tulis pesan dengan jelas agar penerima memahami tujuan notifikasi ini.</div>
                        </div>

                        {{-- URL Aksi (opsional) --}}
                        <div class="mb-3">
                            <label for="url_aksi" class="form-label fw-semibold">
                                Tautan (Opsional)
                            </label>
                            <input type="url" id="url_aksi" name="url_aksi"
                                   class="form-control @error('url_aksi') is-invalid @enderror"
                                   value="{{ old('url_aksi') }}"
                                   placeholder="https://...">
                            @error('url_aksi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Isi jika notifikasi ini terkait halaman tertentu yang perlu dibuka penerima.</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Kanan: Pengaturan --}}
            <div class="col-lg-4">

                {{-- Tipe & Prioritas --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold"><i class="fas fa-sliders-h me-2 text-primary"></i>Pengaturan</h6>
                    </div>
                    <div class="card-body">

                        {{-- Tipe --}}
                        <div class="mb-3">
                            <label for="tipe" class="form-label fw-semibold">
                                Tipe Notifikasi <span class="text-danger">*</span>
                            </label>
                            <select id="tipe" name="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="info"        {{ old('tipe') === 'info'       ? 'selected' : '' }}>
                                    ℹ️ Info — Informasi umum
                                </option>
                                <option value="pengumuman"  {{ old('tipe') === 'pengumuman' ? 'selected' : '' }}>
                                    📢 Pengumuman — Pemberitahuan resmi
                                </option>
                                <option value="peringatan"  {{ old('tipe') === 'peringatan' ? 'selected' : '' }}>
                                    ⚠️ Peringatan — Perlu perhatian
                                </option>
                                <option value="sukses"      {{ old('tipe') === 'sukses'     ? 'selected' : '' }}>
                                    ✅ Sukses — Konfirmasi keberhasilan
                                </option>
                                <option value="sistem"      {{ old('tipe') === 'sistem'     ? 'selected' : '' }}>
                                    ⚙️ Sistem — Notifikasi teknis
                                </option>
                                <option value="error"       {{ old('tipe') === 'error'      ? 'selected' : '' }}>
                                    ❌ Error — Laporan kesalahan
                                </option>
                            </select>
                            @error('tipe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Prioritas --}}
                        <div class="mb-0">
                            <label for="prioritas" class="form-label fw-semibold">
                                Prioritas <span class="text-danger">*</span>
                            </label>
                            <select id="prioritas" name="prioritas" class="form-select @error('prioritas') is-invalid @enderror" required>
                                <option value="rendah"  {{ old('prioritas') === 'rendah'  ? 'selected' : '' }}>🔵 Rendah</option>
                                <option value="sedang"  {{ old('prioritas', 'sedang') === 'sedang' ? 'selected' : '' }}>🟢 Sedang (default)</option>
                                <option value="tinggi"  {{ old('prioritas') === 'tinggi'  ? 'selected' : '' }}>🟠 Tinggi</option>
                                <option value="darurat" {{ old('prioritas') === 'darurat' ? 'selected' : '' }}>🔴 Darurat</option>
                            </select>
                            @error('prioritas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Tujuan Pengiriman --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold"><i class="fas fa-users me-2 text-primary"></i>Tujuan Pengiriman</h6>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label for="tipe_penerima" class="form-label fw-semibold">
                                Kirim Ke <span class="text-danger">*</span>
                            </label>
                            <select id="tipe_penerima" name="tipe_penerima"
                                    class="form-select @error('tipe_penerima') is-invalid @enderror"
                                    onchange="togglePenerimaSeletor(this.value)" required>
                                <option value="">-- Pilih Tujuan --</option>
                                <option value="semua" {{ old('tipe_penerima') === 'semua' ? 'selected' : '' }}>
                                    👥 Semua Pengguna (Guru + Siswa)
                                </option>
                                <option value="guru"  {{ old('tipe_penerima') === 'guru'  ? 'selected' : '' }}>
                                    👨‍🏫 Semua Guru
                                </option>
                                <option value="siswa" {{ old('tipe_penerima') === 'siswa' ? 'selected' : '' }}>
                                    👨‍🎓 Semua Siswa
                                </option>
                                <option value="user"  {{ old('tipe_penerima') === 'user'  ? 'selected' : '' }}>
                                    👤 Pilih User Spesifik
                                </option>
                            </select>
                            @error('tipe_penerima')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Pilih user spesifik (muncul kalau pilih "user") --}}
                        <div id="spesifikSection" style="display:none;">
                            <label class="form-label fw-semibold">
                                Pilih Penerima <span class="text-danger">*</span>
                            </label>

                            {{-- Search input --}}
                            <input type="text" id="searchPenerima" class="form-control form-control-sm mb-2"
                                   placeholder="Cari nama atau email...">

                            <div id="penerimaList" class="border rounded p-2" style="max-height:220px;overflow-y:auto;">
                                <div class="text-muted small mb-1 fw-semibold">GURU</div>
                                @foreach($gurus as $guru)
                                <div class="form-check penerima-item">
                                    <input class="form-check-input" type="checkbox"
                                           name="penerima_ids[]" value="{{ $guru->id }}"
                                           id="penerima_{{ $guru->id }}"
                                           {{ in_array($guru->id, old('penerima_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="penerima_{{ $guru->id }}">
                                        <span class="penerima-name">{{ $guru->name }}</span>
                                        <span class="text-muted penerima-email">— {{ $guru->email }}</span>
                                    </label>
                                </div>
                                @endforeach

                                <div class="text-muted small mb-1 fw-semibold mt-2">SISWA</div>
                                @foreach($siswas as $siswa)
                                <div class="form-check penerima-item">
                                    <input class="form-check-input" type="checkbox"
                                           name="penerima_ids[]" value="{{ $siswa->id }}"
                                           id="penerima_{{ $siswa->id }}"
                                           {{ in_array($siswa->id, old('penerima_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="penerima_{{ $siswa->id }}">
                                        <span class="penerima-name">{{ $siswa->name }}</span>
                                        <span class="text-muted penerima-email">— {{ $siswa->email }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('penerima_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="d-flex justify-content-between mt-1">
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary" onclick="checkAll()">Pilih Semua</button>
                                <button type="button" class="btn btn-link btn-sm p-0 text-muted" onclick="uncheckAll()">Hapus Pilihan</button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Preview & Kirim --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div id="previewSection" class="alert alert-info py-2 small mb-3" style="display:none;">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="previewText">—</span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Notifikasi
                        </button>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            Batal
                        </a>
                    </div>
                </div>

            </div>{{-- end kanan --}}
        </div>{{-- end row --}}
    </form>

</div>
@endsection

@push('scripts')
<script>
function togglePenerimaSeletor(val) {
    const sec = document.getElementById('spesifikSection');
    sec.style.display = (val === 'user') ? 'block' : 'none';
    updatePreview(val);
}

function updatePreview(val) {
    const preview = document.getElementById('previewSection');
    const text    = document.getElementById('previewText');
    const map = {
        'semua': 'Notifikasi akan dikirim ke semua guru dan siswa yang aktif.',
        'guru':  'Notifikasi akan dikirim ke semua guru yang aktif.',
        'siswa': 'Notifikasi akan dikirim ke semua siswa yang aktif.',
        'user':  'Notifikasi akan dikirim hanya ke pengguna yang dipilih.',
    };
    if (map[val]) {
        text.textContent = map[val];
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

function checkAll() {
    document.querySelectorAll('#penerimaList input[type="checkbox"]').forEach(cb => cb.checked = true);
}

function uncheckAll() {
    document.querySelectorAll('#penerimaList input[type="checkbox"]').forEach(cb => cb.checked = false);
}

// Search filter
document.getElementById('searchPenerima')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.penerima-item').forEach(item => {
        const name  = item.querySelector('.penerima-name')?.textContent.toLowerCase() || '';
        const email = item.querySelector('.penerima-email')?.textContent.toLowerCase() || '';
        item.style.display = (name.includes(q) || email.includes(q)) ? '' : 'none';
    });
});

// Init saat page load (restore old value jika validasi gagal)
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('tipe_penerima');
    if (sel.value) togglePenerimaSeletor(sel.value);
});
</script>
@endpush
