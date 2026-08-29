@extends('layouts.siswa')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('page-actions')
    <a href="{{ route('siswa.profile.edit') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-edit me-1"></i>Edit Profil
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
                    @if($student->foto)
                        <img src="{{ asset('storage/'.$student->foto) }}"
                             class="rounded-circle" style="width:80px;height:80px;object-fit:cover;"
                             alt="Foto Profil">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                             style="width:80px;height:80px;font-size:2rem;background:linear-gradient(135deg,#7c3aed,#db2777);">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="mb-1 fw-bold">{{ $user->name }}</h4>
                        <div class="text-muted">{{ $user->email }}</div>
                        @if($student->kelas)
                            <span class="badge mt-1" style="background:#e0f2fe;color:#0891b2;border-radius:20px;">
                                {{ $student->kelas->name }}
                            </span>
                        @endif
                        @if($student->nis)
                            <span class="badge mt-1 ms-1" style="background:#f3e8ff;color:#7c3aed;border-radius:20px;">
                                NIS: {{ $student->nis }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row g-3">
                    @foreach([
                        ['NIS', $student->nis ?? '—'],
                        ['NISN', $student->nisn ?? '—'],
                        ['Kelas', $student->kelas?->name ?? '—'],
                        ['Jenis Kelamin', $student->jenis_kelamin === 'L' ? 'Laki-laki' : ($student->jenis_kelamin === 'P' ? 'Perempuan' : '—')],
                        ['Tempat Lahir', $student->tempat_lahir ?? '—'],
                        ['Tanggal Lahir', $student->tanggal_lahir?->format('d M Y') ?? '—'],
                        ['Alamat', $student->alamat ?? '—'],
                        ['No. Telepon', $user->phone ?? '—'],
                        ['Tahun Ajaran', $student->tahun_ajaran ?? '—'],
                    ] as [$label, $val])
                    <div class="col-md-6">
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
