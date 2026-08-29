@extends('layouts.siswa')

@section('title', 'Data Kesehatan')
@section('page-title', 'Data Kesehatan')

@section('page-actions')
    <a href="{{ route('siswa.profile.edit') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-edit me-1"></i>Edit Profil
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:14px 14px 0 0;">
                <h6 class="mb-0 fw-bold"><i class="fas fa-heartbeat me-2 text-danger"></i>Data Kesehatan Siswa</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach([
                        ['Golongan Darah', $student->golongan_darah ?? '—'],
                        ['Riwayat Penyakit', $student->riwayat_penyakit ?? '—'],
                        ['Alergi', $student->alergi ?? '—'],
                        ['Info Kesehatan Lain', $student->info_kesehatan ?? '—'],
                        ['Nama Orang Tua/Wali', $student->nama_ortu ?? '—'],
                        ['No. Telepon Orang Tua', $student->no_telepon_ortu ?? '—'],
                    ] as [$label, $val])
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small mb-1">{{ $label }}</div>
                            <div class="fw-semibold">{{ $val }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
