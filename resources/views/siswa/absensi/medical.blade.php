@extends('layouts.siswa')

@section('title', 'Rekam Medis')
@section('page-title', 'Rekam Medis')
@section('page-subtitle', 'Informasi kesehatan dan riwayat medis.')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-notes-medical me-2"></i>Catatan Sakit & Izin</h6>
        <span class="badge bg-white text-info">{{ $medicalRecords->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th class="text-center">Status</th>
                        <th>Catatan</th>
                        <th>Dicatat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicalRecords as $record)
                        <tr>
                            <td class="ps-3 fw-semibold">
                                {{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}
                            </td>
                            <td class="text-center">
                                @if($record->status == 'sakit')
                                    <span class="badge bg-warning text-dark">Sakit</span>
                                @elseif($record->status == 'izin')
                                    <span class="badge bg-info">Izin</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($record->status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $record->note ?? '—' }}</td>
                            <td class="text-muted">{{ $record->created_at?->format('d M Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-notes-medical fa-2x text-muted opacity-25 mb-3 d-block"></i>
                                <p class="text-muted mb-0">Tidak ada catatan sakit atau izin.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($medicalRecords->hasPages())
        <div class="card-footer bg-white border-top">{{ $medicalRecords->links() }}</div>
    @endif
</div>

@endsection
