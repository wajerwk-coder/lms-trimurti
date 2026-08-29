@extends('layouts.siswa')

@section('title', 'Riwayat Pengumpulan')
@section('page-title', 'Riwayat Pengumpulan')
@section('page-subtitle', 'Semua tugas yang pernah kamu kumpulkan.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.assignments.index') }}">Tugas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Riwayat</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-body p-0">
        @if($submissions->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-history fa-2x opacity-25 mb-2 d-block"></i>
            Belum ada riwayat pengumpulan tugas.
        </div>
        @else
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:.85rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Tugas</th>
                        <th class="py-3">Mata Pelajaran</th>
                        <th class="py-3">Dikumpulkan</th>
                        <th class="text-center py-3">Nilai</th>
                        <th class="text-center pe-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $sub)
                    @php
                        $score = (float)($sub->score ?? 0);
                        $sc    = $score >= 80 ? '#16a34a' : ($score >= 60 ? '#d97706' : '#dc2626');
                    @endphp
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $sub->assignment?->title ?? '—' }}</td>
                        <td class="text-muted">{{ $sub->assignment?->subject?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $sub->submitted_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td class="text-center">
                            @if($sub->score !== null)
                                <span class="fw-bold" style="color:{{ $sc }};">{{ number_format($score, 0) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            @if($sub->score !== null)
                                <span class="badge" style="background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.68rem;">Dinilai</span>
                            @else
                                <span class="badge" style="background:#fef9c3;color:#a16207;border-radius:20px;font-size:.68rem;">Menunggu</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top">{{ $submissions->links() }}</div>
        @endif
    </div>
</div>
@endsection
