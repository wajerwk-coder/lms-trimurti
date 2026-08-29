@php
    // Expected variables: $practical, $siswa, $criterias (Collection of Criteria)
@endphp

<div class="bg-white rounded-lg shadow border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-dark">Checklist SOP Penilaian Praktik</h3>
            <p class="text-sm text-muted">Centang indikator yang tercapai. Nilai akan dihitung otomatis.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('guru.scoring.auto', [$practical->id, $siswa->id]) }}" class="px-6 py-5 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($criterias as $criteria)
                <label class="flex items-start p-3 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100">
                    <input type="checkbox"
                           name="scores_checklist[]"
                           value="{{ $criteria->id }}"
                           class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ms-3">
                        <span class="block text-sm font-medium text-gray-900">{{ $criteria->name ?? ('Kriteria #' . $criteria->id) }}</span>
                        <span class="block text-xs text-muted">Bobot maksimum: {{ $criteria->max_score }}</span>
                        @if(!empty($criteria->description))
                            <span class="block text-xs text-muted mt-1">{{ $criteria->description }}</span>
                        @endif
                    </span>
                </label>
            @empty
                <div class="col-span-2">
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm">
                        Belum ada kriteria aktif untuk mata pelajaran ini.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label text-sm text-gray-700">Status Kehadiran</label>
                <select name="attendance_status" class="form-input">
                    <option value="hadir">Hadir</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alpha">Alpha</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="form-label text-sm text-gray-700">Catatan/Feedback (opsional)</label>
                <input type="text" name="feedback" class="form-input" placeholder="Contoh: SOP dilakukan dengan rapi, komunikasi baik" />
            </div>
        </div>

        <div class="pt-3 border-t border-gray-200 flex items-center justify-end">
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Generate Nilai Otomatis
            </button>
        </div>
    </form>
</div>


