@extends('layouts.public')

@section('title', 'Laporan terkirim - Temuin Aja')

@section('content')
    <section class="confirmation page-container" aria-labelledby="confirmation-title">
        <div class="confirmation-art" aria-hidden="true">@include('illustrations.review')</div>
        <span class="eyebrow">TERIMA KASIH SUDAH MELAPOR</span>
        <h1 class="page-title" id="confirmation-title">Kabar kamu<br><em>sudah kami terima</em></h1>
        <p class="page-intro">Petugas akan memeriksa laporanmu sebelum ditampilkan di katalog</p>
        <div class="confirmation-ticket surface">
            <span class="field-hint">Kode laporanmu</span>
            <code>{{ $laporan->kode_laporan }}</code>
            <span class="status-badge"
                data-status="{{ $laporan->status_laporan }}">{{ ucfirst(str_replace('_', ' ', $laporan->status_laporan)) }}</span>
            <p>Simpan kode ini untuk referensi saat menghubungi petugas akademik</p>
        </div>
        @if ($laporan->jenis_laporan === 'ditemukan')
            <p class="confirmation-note">Simpan barang dengan aman. Petugas akan menghubungimu untuk
                mengatur langkah berikutnya setelah laporan diperiksa</p>
        @endif
        <div class="actions">
            <a class="button accent" href="{{ route('home') }}">Kembali ke beranda</a>
            <a class="text-link" href="{{ route('laporan.index', 'ditemukan') }}">Lihat barang
                temuan</a>
        </div>
    </section>
@endsection
