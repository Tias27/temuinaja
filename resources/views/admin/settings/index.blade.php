@extends('layouts.admin')

@section('title', 'Pengaturan admin')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="eyebrow">AKUN &amp; TIM PETUGAS</span>
            <h1 class="page-title">Ruang kerja,<br><em>lebih personal</em></h1>
            <p class="page-intro">Atur profilmu dan tambahkan petugas untuk membantu mengelola laporan
            </p>
        </div>
        <div class="admin-profile-chip">
            <span class="admin-avatar"
                aria-hidden="true">{{ mb_strtoupper(mb_substr($admin->nama_admin, 0, 1)) }}</span>
            <div><strong>{{ $admin->nama_admin }}</strong><span>{{ $admin->nama_pengguna }}</span></div>
        </div>
    </div>

    <nav class="admin-settings-tabs" aria-label="Pengaturan akun">
        <a href="{{ route('admin.settings.index') }}"
            @if ($tab === 'profile') aria-current="page" @endif>Profil saya</a>
        <a href="{{ route('admin.settings.index', ['tab' => 'admins']) }}"
            @if ($tab === 'admins') aria-current="page" @endif>Kelola admin</a>
    </nav>

    @if ($tab === 'profile')
        @include('admin.settings.profile')
    @else
        @include('admin.settings.admins')
    @endif
@endsection
