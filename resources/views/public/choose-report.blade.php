@extends('layouts.public')

@section('title', 'Buat laporan - Temuin Aja')

@section('content')
    <section class="report-choice-page page-container" aria-labelledby="report-choice-title">
        <div class="report-choice-heading">
            <span class="eyebrow">MULAI DARI SINI</span>
            <h1 class="page-title" id="report-choice-title">Mau melaporkan<br><em>apa hari ini?</em></h1>
            <p class="page-intro">Pilih sesuai keadaanmu, lalu ceritakan detail barangnya</p>
        </div>
        <div class="report-choices">
            <a class="report-choice" href="{{ route('laporan.create', 'hilang') }}"
                aria-labelledby="choice-lost-title" aria-describedby="choice-lost-description">
                <div class="report-choice-art" aria-hidden="true">
                    @include('illustrations.report')
                </div>
                <div class="report-choice-copy">
                    <span class="report-choice-kicker">BANTU CARI BARANGKU</span>
                    <h2 id="choice-lost-title">Saya kehilangan barang</h2>
                    <p id="choice-lost-description">Barang milikmu hilang atau tertinggal di kampus?
                        Beri tahu cirinya supaya petugas bisa bantu mencari</p>
                </div>
                <span class="report-choice-action">Laporkan kehilangan
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
            <a class="report-choice report-choice-found"
                href="{{ route('laporan.create', 'ditemukan') }}" aria-labelledby="choice-found-title"
                aria-describedby="choice-found-description">
                <div class="report-choice-art" aria-hidden="true">
                    @include('illustrations.return')
                </div>
                <div class="report-choice-copy">
                    <span class="report-choice-kicker">BANTU BARANG KEMBALI</span>
                    <h2 id="choice-found-title">Saya menemukan barang</h2>
                    <p id="choice-found-description">Menemukan barang yang bukan milikmu? Laporkan
                        temuannya agar bisa kembali ke pemiliknya</p>
                </div>
                <span class="report-choice-action">Laporkan temuan
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
        </div>
        <p class="report-choice-help">Mau cek apakah barangmu sudah ditemukan? <a class="text-link"
                href="{{ route('laporan.index', 'ditemukan') }}">Lihat barang temuan <span
                    aria-hidden="true">&rarr;</span></a></p>
    </section>
@endsection
