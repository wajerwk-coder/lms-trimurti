@extends('layouts.siswa')

@section('title', 'Riwayat Unduhan Materi')
@section('page-title', 'Riwayat Unduhan')
@section('page-subtitle', 'Materi yang pernah kamu unduh.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('siswa.materials.index') }}">Materi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Riwayat Unduhan</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-body p-0">
        @if($downloads->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-download fa-2x opacity-25 mb-2 d-block"></i>
            Belum ada riwayat unduhan.
        </div>
        @else
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:.85rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Materi</th>
                        <th class="py-3">Mata Pelajaran</th>
                        <th class="py-3">Tanggal Unduh</th>
                        <th class="text-center pe-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($downloads as $dl)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $dl->material?->title ?? '—' }}</td>
                        <td class="text-muted">{{ $dl->material?->subject?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $dl->downloaded_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td class="text-center pe-4">
                            @if($dl->material)
                            <a href="{{ route('siswa.materials.show', $dl->material->id) }}"
                               class="btn btn-sm btn-outline-primary" style="border-radius:7px;">
                                <i class="fas fa-eye" style="font-size:.7rem;"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top">{{ $downloads->links() }}</div>
        @endif
    </div>
</div>
@endsection
