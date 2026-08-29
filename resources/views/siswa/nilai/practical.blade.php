@extends('layouts.siswa')

@section('title', 'Nilai Praktikum - LMS Trimurti Husada')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2 class="mb-0">Nilai Praktikum</h2>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Rata-rata</span>
                        <strong>{{ number_format($stats['average_score'] ?? $stats['practical_avg'] ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Nilai</span>
                        <strong>{{ $stats['total_scores'] ?? $stats['total_practical_scores'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Tertinggi</span>
                        <strong>{{ $stats['highest_score'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Terendah</span>
                        <strong>{{ $stats['lowest_score'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Praktikum</th>
                        <th>Kriteria</th>
                        <th>Nilai</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scores as $item)
                        <tr>
                            <td>{{ optional($item->practical)->title ?? '-' }}</td>
                            <td>{{ optional($item->criteria)->name }}</td>
                            <td>{{ $item->score }}</td>
                            <td>{{ optional($item->created_at)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">Belum ada nilai praktikum.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $scores->links() }}
        </div>
    </div>
</div>
@endsection
