{{--
    Partial: Pemilihan Mata Pelajaran per Jurusan
    Variabel yang dibutuhkan:
      - $allSubjects      : Collection MataPelajaran (with jurusan), semua aktif
      - $jurusanMap       : Collection Jurusan dikeyBy('id')
      - $selectedSubjectIds : array id yang sudah dipilih (kosong array di create)
      - $kelasJurusanId   : jurusan_id kelas saat ini (null di create, nilai di edit)
--}}
@php
    $selectedSubjectIds = $selectedSubjectIds ?? [];
    $kelasJurusanId     = $kelasJurusanId ?? null;

    // Group semua subject per major_id
    $subjectGroups = $allSubjects->groupBy(fn($s) => $s->major_id ?? 0);

    $groupColors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
@endphp

<div class="card border-0 shadow-sm mt-4" id="subjectPickerCard">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <span class="rounded-2 p-2 bg-success bg-opacity-10 lh-1">
                    <i class="fas fa-book-open text-success"></i>
                </span>
                <div>
                    <h6 class="mb-0 fw-semibold">Mata Pelajaran</h6>
                    <small class="text-muted">Pilih mata pelajaran untuk kelas ini, dikelompokkan per jurusan</small>
                </div>
            </div>
            <span class="badge bg-primary rounded-pill" id="selectedCount">
                {{ count($selectedSubjectIds) }} dipilih
            </span>
        </div>
    </div>

    <div class="card-body pb-2">

        {{-- Filter cepat --}}
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control" id="subjectSearch"
                           placeholder="Cari mata pelajaran...">
                </div>
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllVisible">
                    <i class="fas fa-check-double me-1"></i>Pilih Semua Terlihat
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="clearAll">
                    <i class="fas fa-times me-1"></i>Hapus Semua
                </button>
            </div>
        </div>

        {{-- Groups per jurusan --}}
        @foreach($subjectGroups as $gJurusanId => $subjects)
            @php
                $colorIdx  = (int) array_search($gJurusanId, $subjectGroups->keys()->toArray());
                $color     = $groupColors[$colorIdx % count($groupColors)];
                $jur       = $gJurusanId ? $jurusanMap->get($gJurusanId) : null;
                $jurName   = $jur?->name ?? 'Umum / Tanpa Jurusan';
                $jurCode   = $jur?->code ?? null;
                $isMatch   = $kelasJurusanId && (int)$gJurusanId === (int)$kelasJurusanId;
            @endphp

            <div class="subject-group mb-3 border rounded-3 overflow-hidden"
                 data-jurusan-id="{{ $gJurusanId }}"
                 style="{{ $kelasJurusanId && !$isMatch ? 'opacity:.45;' : '' }}">

                {{-- Header group --}}
                <div class="d-flex align-items-center justify-content-between px-3 py-2
                            bg-{{ $color }} bg-opacity-10"
                     role="button"
                     onclick="toggleGroup(this)">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-graduation-cap text-{{ $color }} flex-shrink-0"></i>
                        <span class="fw-semibold small text-{{ $color }}">
                            {{ $jurName }}
                            @if($jurCode) <span class="fw-normal opacity-75">({{ $jurCode }})</span> @endif
                        </span>
                        @if($isMatch)
                            <span class="badge bg-{{ $color }} ms-1" style="font-size:.65rem;">
                                Jurusan kelas ini
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} group-count"
                              data-group="{{ $gJurusanId }}">
                            {{ $subjects->count() }} mapel
                        </span>
                        <i class="fas fa-chevron-down text-muted small group-chevron" style="transition:.2s;"></i>
                    </div>
                </div>

                {{-- Checkbox list --}}
                <div class="subject-group-body px-3 py-2"
                     style="{{ !$isMatch && $kelasJurusanId ? 'display:none;' : '' }}">
                    <div class="row g-2">
                        @foreach($subjects->sortBy('name') as $subject)
                        <div class="col-md-6 subject-item"
                             data-name="{{ strtolower($subject->name) }}"
                             data-code="{{ strtolower($subject->code ?? '') }}"
                             data-jurusan="{{ $gJurusanId }}">
                            <label class="d-flex align-items-start gap-2 p-2 rounded-2 subject-label
                                          {{ in_array($subject->id, $selectedSubjectIds) ? 'bg-'.$color.' bg-opacity-10' : '' }}"
                                   style="cursor:pointer;border:1px solid transparent;transition:.15s;"
                                   data-color="{{ $color }}">
                                <input type="checkbox"
                                       name="subject_ids[]"
                                       value="{{ $subject->id }}"
                                       class="form-check-input flex-shrink-0 mt-1 subject-check"
                                       {{ in_array($subject->id, $selectedSubjectIds) ? 'checked' : '' }}>
                                <div class="lh-sm">
                                    <div class="fw-semibold small">{{ $subject->name }}</div>
                                    <div class="d-flex gap-1 flex-wrap mt-1">
                                        @if($subject->code)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary"
                                              style="font-size:.65rem;">{{ $subject->code }}</span>
                                        @endif
                                        @php
                                            $tc = match($subject->type) {
                                                'teori'     => ['info',    'Teori'],
                                                'praktikum' => ['warning', 'Praktikum'],
                                                default     => ['primary', 'Campuran'],
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $tc[0] }} bg-opacity-10 text-{{ $tc[0] }}"
                                              style="font-size:.65rem;">{{ $tc[1] }}</span>
                                        @if($subject->sks)
                                        <span class="badge bg-light text-muted"
                                              style="font-size:.65rem;">{{ $subject->sks }} SKS</span>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        @endforeach

    </div>
</div>

@push('js')
<script>
(function () {
    // ── Toggle group buka/tutup ─────────────────────────────────────────
    window.toggleGroup = function (header) {
        const body    = header.nextElementSibling;
        const chevron = header.querySelector('.group-chevron');
        const open    = body.style.display !== 'none';
        body.style.display    = open ? 'none' : '';
        chevron.style.transform = open ? 'rotate(-90deg)' : 'rotate(0)';
    };

    // ── Saat jurusan kelas dipilih: highlight group yang relevan ────────
    const majorSelect = document.getElementById('majorSelect');
    if (majorSelect) {
        majorSelect.addEventListener('change', function () {
            const selectedId = this.value;
            document.querySelectorAll('.subject-group').forEach(function (grp) {
                const gid   = grp.dataset.jurusanId;
                const body  = grp.querySelector('.subject-group-body');
                const chev  = grp.querySelector('.group-chevron');
                const match = selectedId && gid === selectedId;

                grp.style.opacity = (!selectedId || match) ? '1' : '0.45';

                if (match) {
                    body.style.display     = '';
                    chev.style.transform   = 'rotate(0)';
                } else if (selectedId) {
                    body.style.display   = 'none';
                    chev.style.transform = 'rotate(-90deg)';
                }
            });
        });

        // Trigger sekali saat load jika sudah ada nilai terpilih
        if (majorSelect.value) majorSelect.dispatchEvent(new Event('change'));
    }

    // ── Checkbox visual feedback ────────────────────────────────────────
    function updateLabel(checkbox) {
        const label = checkbox.closest('label');
        const color = label?.dataset?.color ?? 'primary';
        if (checkbox.checked) {
            label.classList.add('bg-' + color, 'bg-opacity-10');
            label.style.borderColor = 'var(--bs-' + color + ')';
        } else {
            label.classList.remove('bg-' + color, 'bg-opacity-10');
            label.style.borderColor = 'transparent';
        }
    }

    function updateCounter() {
        const total = document.querySelectorAll('.subject-check:checked').length;
        const badge = document.getElementById('selectedCount');
        if (badge) badge.textContent = total + ' dipilih';
    }

    document.querySelectorAll('.subject-check').forEach(function (cb) {
        updateLabel(cb); // init state
        cb.addEventListener('change', function () {
            updateLabel(this);
            updateCounter();
        });
    });
    updateCounter();

    // ── Tombol pilih semua visible ──────────────────────────────────────
    document.getElementById('selectAllVisible')?.addEventListener('click', function () {
        document.querySelectorAll('.subject-item:not([style*="display: none"]) .subject-check').forEach(function (cb) {
            cb.checked = true;
            updateLabel(cb);
        });
        updateCounter();
    });

    // ── Tombol hapus semua ──────────────────────────────────────────────
    document.getElementById('clearAll')?.addEventListener('click', function () {
        if (!confirm('Hapus semua pilihan mata pelajaran?')) return;
        document.querySelectorAll('.subject-check').forEach(function (cb) {
            cb.checked = false;
            updateLabel(cb);
        });
        updateCounter();
    });

    // ── Pencarian real-time ─────────────────────────────────────────────
    document.getElementById('subjectSearch')?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.subject-item').forEach(function (item) {
            const match = !q || item.dataset.name.includes(q) || item.dataset.code.includes(q);
            item.style.display = match ? '' : 'none';
        });

        // Update group count text
        document.querySelectorAll('.subject-group').forEach(function (grp) {
            const visible = grp.querySelectorAll('.subject-item:not([style*="display: none"])').length;
            const countEl = document.querySelector('.group-count[data-group="' + grp.dataset.jurusanId + '"]');
            if (countEl) countEl.textContent = visible + ' mapel';
            grp.style.display = visible === 0 ? 'none' : '';
        });
    });
})();
</script>
@endpush
