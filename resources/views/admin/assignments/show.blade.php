@extends('layouts.admin')

@section('title', 'Detail Tugas')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.assignments.index') }}">Tugas & Quiz</a></li>
    <li class="breadcrumb-item active">Detail Tugas</li>
@endsection

@section('page-title', 'Detail Tugas')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('admin.assignments.edit', $assignment) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <button type="button" class="btn btn-{{ $assignment->is_published ? 'secondary' : 'success' }}"
                onclick="togglePublish({{ $assignment->id }})">
            <i class="fas fa-{{ $assignment->is_published ? 'eye-slash' : 'eye' }} me-1"></i>
            {{ $assignment->is_published ? 'Unpublish' : 'Publish' }}
        </button>
        <button type="button" class="btn btn-danger" onclick="deleteAssignment({{ $assignment->id }})">
            <i class="fas fa-trash me-1"></i> Hapus
        </button>
    </div>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold"><i class="fas fa-tasks me-2"></i>Informasi Tugas</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1">{{ $assignment->title }}</h5>
                <p class="text-muted small mb-3">
                    <i class="fas fa-user me-1"></i>{{ $assignment->guru?->name ?? 'N/A' }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-calendar me-1"></i>{{ $assignment->created_at->format('d/m/Y H:i') }}
                </p>

                <h6 class="fw-bold">Deskripsi</h6>
                <div class="bg-light rounded-3 p-3 mb-3 small">
                    {!! nl2br(e($assignment->description)) !!}
                </div>

                @if($assignment->file_url)
                <div class="d-flex align-items-center p-3 bg-light rounded-3 mb-3">
                    <i class="fas fa-paperclip text-primary me-2"></i>
                    <span class="small flex-grow-1">{{ basename($assignment->file_url) }}</span>
                    <a href="{{ asset('storage/assignments/' . $assignment->file_url) }}"
                       class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fas fa-download me-1"></i>Unduh
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Submissions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-inbox me-2"></i>Submissions</h6>
                <span class="badge bg-white text-success">{{ $assignment->submissions->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($assignment->submissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Siswa</th>
                                    <th>Tanggal Submit</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignment->submissions as $submission)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $submission->siswa?->name ?? 'N/A' }}</td>
                                    <td class="text-muted">
                                        {{ $submission->submitted_at?->format('d/m/Y H:i') ?? 'Belum submit' }}
                                    </td>
                                    <td class="text-center">
                                        @if($submission->submitted_at)
                                            <span class="badge bg-success">Submitted</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($submission->score !== null)
                                            <span class="badge bg-primary">{{ $submission->score }}/{{ $assignment->max_score }}</span>
                                        @else
                                            <span class="badge bg-secondary">Belum dinilai</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted opacity-25 mb-3 d-block"></i>
                        <p class="text-muted small mb-0">Belum ada submission.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Ringkasan</h6>
            </div>
            <div class="card-body small">
                <div class="mb-2">
                    <span class="text-muted">Status</span>
                    <div class="mt-1">
                        @if($assignment->is_published)
                            <span class="badge bg-success">Dipublikasikan</span>
                        @else
                            <span class="badge bg-warning text-dark">Draft</span>
                        @endif
                    </div>
                </div>
                <hr class="my-2">
                <div class="mb-2">
                    <span class="text-muted">Deadline</span>
                    <div class="mt-1">
                        @if($assignment->due_date)
                            <span class="badge bg-{{ $assignment->due_date->isPast() ? 'danger' : 'info' }}">
                                {{ $assignment->due_date->format('d/m/Y H:i') }}
                            </span>
                            @if($assignment->due_date->isPast())
                                <small class="text-danger d-block mt-1">Deadline telah lewat</small>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Nilai Maksimal</span>
                    <span class="fw-semibold">{{ $assignment->max_score }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Submissions</span>
                    <span class="fw-semibold">{{ $assignment->submissions->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Sudah Dinilai</span>
                    <span class="fw-semibold text-success">{{ $assignment->submissions->whereNotNull('score')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Rata-rata Nilai</span>
                    <span class="fw-semibold text-primary">
                        @php
                            $scored = $assignment->submissions->whereNotNull('score');
                            echo $scored->count() > 0 ? number_format($scored->avg('score'), 1) : '—';
                        @endphp
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-muted">Apakah Anda yakin ingin menghapus tugas ini? Tindakan ini tidak dapat dibatalkan.</div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection