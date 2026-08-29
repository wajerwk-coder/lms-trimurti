@extends('layouts.admin')

@section('title', 'Detail Kriteria Penilaian')
@section('page-title', 'Detail Kriteria Penilaian')
@section('page-subtitle', $kriteriaPenilaian->name . ' — ' . ($kriteriaPenilaian->mata_praktik ?? ''))

@section('page-actions')
    <a href="{{ route('admin.kriteria-penilaian.edit', $kriteriaPenilaian->id) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit me-1"></i>Edit
    </a>
    <a href="{{ route('admin.kriteria-penilaian.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-clipboard-check me-2"></i>{{ $kriteriaPenilaian->name }}
                </h6>
                <span class="badge bg-white text-info">{{ $kriteriaPenilaian->tingkat_kelas ?? '—' }}</span>
            </div>

            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Nama Kriteria</div>
                        <div class="fw-semibold">{{ $kriteriaPenilaian->name }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Kategori</div>
                        @php
                            $katColors = ['persiapan'=>'info','pelaksanaan'=>'primary','hasil'=>'success','sikap'=>'warning'];
                            $katLabels = ['persiapan'=>'Persiapan','pelaksanaan'=>'Pelaksanaan','hasil'=>'Hasil','sikap'=>'Sikap Profesional'];
                            $kat = $kriteriaPenilaian->kategori ?? '';
                        @endphp
                        <span class="badge bg-{{ $katColors[$kat] ?? 'secondary' }} {{ $kat==='sikap'?'text-dark':'' }}">
                            {{ $katLabels[$kat] ?? ucfirst($kat ?: '—') }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Bobot</div>
                        <div class="fw-semibold fs-5">{{ $kriteriaPenilaian->weight }}%</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Mata Praktik</div>
                        <div class="fw-semibold">{{ $kriteriaPenilaian->mata_praktik ?? '—' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Tingkat Kelas</div>
                        <div class="fw-semibold">{{ $kriteriaPenilaian->tingkat_kelas ?? '—' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Status</div>
                        @if($kriteriaPenilaian->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </div>

                    @if($kriteriaPenilaian->description)
                    <div class="col-12">
                        <div class="text-muted small mb-1">Deskripsi</div>
                        <div class="bg-light rounded-2 p-3 small">{{ $kriteriaPenilaian->description }}</div>
                    </div>
                    @endif
                </div>

                <hr>

                {{-- SOP Checklist --}}
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-semibold mb-0">
                            <i class="fas fa-list-check me-2 text-primary"></i>SOP Checklist
                        </h6>
                        @php $checklist = $kriteriaPenilaian->sop_checklist ?? [] @endphp
                        @if(is_array($checklist))
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                {{ count($checklist) }} item
                            </span>
                        @endif
                    </div>

                    @if(is_array($checklist) && count($checklist))
                        <ol class="list-group list-group-numbered">
                            @foreach($checklist as $item)
                                <li class="list-group-item border-0 border-bottom d-flex align-items-start gap-2 ps-4">
                                    <i class="fas fa-check-circle text-success mt-1 flex-shrink-0"></i>
                                    <span class="small">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <div class="text-muted fst-italic small">Belum ada checklist.</div>
                    @endif
                </div>

                @if($kriteriaPenilaian->created_at)
                <div class="mt-4 text-muted small">
                    <i class="fas fa-clock me-1"></i>
                    Dibuat: {{ $kriteriaPenilaian->created_at->format('d M Y') }}
                    @if($kriteriaPenilaian->updated_at && $kriteriaPenilaian->updated_at->gt($kriteriaPenilaian->created_at))
                        · Diperbarui: {{ $kriteriaPenilaian->updated_at->format('d M Y H:i') }}
                    @endif
                </div>
                @endif
            </div>

            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
                <a href="{{ route('admin.kriteria-penilaian.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.kriteria-penilaian.edit', $kriteriaPenilaian->id) }}"
                       class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <form action="{{ route('admin.kriteria-penilaian.destroy', $kriteriaPenilaian->id) }}"
                          method="POST"
                          onsubmit="return confirm('Hapus kriteria ini? Tindakan tidak dapat dibatalkan.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
