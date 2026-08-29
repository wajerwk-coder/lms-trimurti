@extends('layouts.admin')

@section('title', 'Detail Mata Pelajaran')
@section('page-title', 'Detail Mata Pelajaran')
@section('page-subtitle', $mataPelajaran->name . ' (' . $mataPelajaran->code . ')')

@section('page-actions')
    <a href="{{ route('admin.mata-pelajaran.edit', $mataPelajaran->id) }}" class="btn btn-sm btn-warning me-1">
        <i class="fas fa-edit me-1"></i> Edit
    </a>
    <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-info text-white d-flex align-items-center">
                <i class="fas fa-book me-2"></i>
                <h5 class="mb-0">Informasi Mata Pelajaran</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Kode</div>
                        <span class="badge bg-secondary fs-6">{{ $mataPelajaran->code }}</span>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Nama</div>
                        <div class="fw-semibold">{{ $mataPelajaran->name }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Jenis</div>
                        @php
                            $typeColors = ['teori'=>'info','praktikum'=>'warning','campuran'=>'success'];
                            $typeLabels = ['teori'=>'Teori','praktikum'=>'Praktikum','campuran'=>'Campuran'];
                        @endphp
                        <span class="badge bg-{{ $typeColors[$mataPelajaran->type] ?? 'secondary' }}">
                            {{ $typeLabels[$mataPelajaran->type] ?? $mataPelajaran->type }}
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">SKS</div>
                        <div class="fw-semibold">{{ $mataPelajaran->sks ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Status</div>
                        @if($mataPelajaran->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Dibuat</div>
                        <div>{{ $mataPelajaran->created_at?->format('d M Y') ?? '-' }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small mb-1">Deskripsi</div>
                    <div>{{ $mataPelajaran->description ?: 'Tidak ada deskripsi.' }}</div>
                </div>

                @if($mataPelajaran->updated_at && $mataPelajaran->updated_at->gt($mataPelajaran->created_at))
                <div class="text-muted small">
                    Terakhir diperbarui: {{ $mataPelajaran->updated_at->format('d M Y H:i') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Aksi Cepat</h6>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('admin.mata-pelajaran.edit', $mataPelajaran->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Edit Mata Pelajaran
                </a>

                <form action="{{ route('admin.mata-pelajaran.toggle-status', $mataPelajaran->id) }}" method="POST"
                      onsubmit="return confirm('Ubah status mata pelajaran ini?')">
                    @csrf
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-power-off me-2"></i>
                        {{ $mataPelajaran->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>

                <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-2"></i>Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection