@extends('layouts.admin')

@section('title', 'Masuk petugas')

@section('content')
    <div class="admin-login-layout">
        <div class="admin-login-intro">
            <span class="eyebrow">RUANG PETUGAS AKADEMIK</span>
            <h1 class="page-title">Bantu barang<br><em>kembali pulang</em></h1>
            <p class="page-intro">Periksa laporan, catat barang temuan, dan bantu pemilik mendapatkan
                barangnya kembali</p>
            <div class="admin-login-art" aria-hidden="true">@include('illustrations.review')</div>
        </div>
        <section class="surface admin-login">
            <span class="eyebrow">SELAMAT DATANG KEMBALI</span>
            <h2 class="admin-title">Masuk petugas</h2>
            <p class="admin-muted">Gunakan akun petugas yang sudah terdaftar</p>
            <form method="post" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="field">
                    <label for="nama-pengguna">Nama pengguna</label>
                    <input id="nama-pengguna" name="nama_pengguna" value="{{ old('nama_pengguna') }}"
                        required autocomplete="username">
                </div>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" required
                        autocomplete="current-password">
                </div>
                <button class="button accent admin-login-submit" type="submit">Masuk ke ruang petugas
                    <span aria-hidden="true">&rarr;</span></button>
            </form>
            <p class="admin-login-note">Akses khusus petugas untuk mengelola laporan kampus</p>
        </section>
    </div>
@endsection
