@extends('layouts.siswa')

@section('title', 'Tugas Diarsipkan')
@section('page-title', 'Tugas Diarsipkan')
@section('page-subtitle', 'Tugas yang sudah melewati deadline.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.assignments.index') }}">Tugas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Diarsipkan</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-body p-0">
        @if($assignments->isEmpty())
        <div class="text-center py-5 text-muted">
            <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;">
                <i class="fas fa-archive text-secondary fa-lg opacity-75"></i>
            </div>
            <h6 class="text-muted mb-1">Tidak Ada Tugas Diarsipkan</h6>
            <p class="small mb-3">Tugas yang sudah lewat deadline akan tampil di sini.</p>
            <a href="{{ route('siswa.assignments.index') }}" class="btn btn-outline-primary btn-sm">
                Lihat Semua Tugas
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:.85rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Judul Tugas</th>
                        <th class="py-3">Mata Pelajaran</th>
                        <th class="py-3">Deadline</th>
                        <th class="text-center pe-4 py-3">Status Kumpul</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $asgn)
                    @php
                        $ucId       = auth()->id();
                        $submitted  = $asgn->submissions->first();
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('siswa.assignments.show', $asgn->id) }}"
                               class="fw-semibold text-dark text-decoration-none">
                                {{ $asgn->title }}
                            </a>
                        </td>
                        <td class="text-muted">{{ $asgn->subject?->name ?? '—' }}</td>
                        <td style="color:#dc2626;font-size:.8rem;">
                            <i class="fas fa-calendar-times me-1"></i>
                            {{ $asgn->due_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="text-center pe-4">
                            @if($submitted)
                                <span class="badge" style="background:#dcfce7;color:#16a34a;border-radius:20px;font-size:.68rem;">
                                    <i class="fas fa-check me-1"></i>Sudah Dikumpulkan
                                </span>
                            @else
                                <span class="badge" style="background:#fee2e2;color:#dc2626;border-radius:20px;font-size:.68rem;">
                                    <i class="fas fa-times me-1"></i>Tidak Dikumpulkan
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())
        <div class="px-4 py-3 border-top">{{ $assignments->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection
