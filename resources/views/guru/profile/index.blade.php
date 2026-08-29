{{-- Redirect ke guru.profile.edit yang lebih lengkap --}}
@extends('layouts.guru')
@section('title', 'Profil Guru')
@section('content')
<script>window.location.href = '{{ route("guru.profile.edit") }}';</script>
@endsection
