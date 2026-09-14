{{--
    View ini sebagai fallback redirect ke halaman edit profil.
    Route guru.profile.index mengarah langsung ke controller edit().
--}}
@extends('layouts.guru')
@section('title', 'Profil Guru')
@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height:200px;">
    <div class="text-center">
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <p class="text-muted">Mengalihkan ke halaman profil...</p>
        <a href="{{ route('guru.profile.edit') }}" class="btn btn-primary btn-sm">
            Klik di sini jika tidak dialihkan otomatis
        </a>
    </div>
</div>
<script>
(function() {
    // Redirect cepat tanpa flash layar kosong
    window.location.replace('{{ route("guru.profile.edit") }}');
})();
</script>
@endsection
