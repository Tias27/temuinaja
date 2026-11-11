@extends('layouts.public')

@section('title')
    {{ $jenis === 'ditemukan' ? 'Barang ditemukan' : 'Barang hilang' }} - Temuin Aja
@endsection

@section('content')
    <section class="catalog" aria-labelledby="catalog-title">
        <div class="catalog-heading">
            <div>
                <span class="eyebrow">KATALOG BARANG</span>
                <h1 class="page-title" id="catalog-title">
                    @if ($jenis === 'ditemukan')
                        Barang ketemu,<br><em>pemiliknya ditunggu</em>
                    @else
                        Bantu temukan<br><em>jalan pulangnya</em>
                    @endif
                </h1>
                <p class="page-intro">
                    {{ $jenis === 'ditemukan' ? 'Telusuri barang temuan di sekitar kampus. Kenali cirinya, lalu hubungi petugas untuk memastikan kepemilikan' : 'Ada yang sedang mencari barangnya. Cek laporan kehilangan dan bantu sampaikan kabar baiknya' }}
                </p>
            </div>
            <div class="catalog-art" aria-hidden="true">
                @include(
                    $jenis === 'ditemukan'
                        ? 'illustrations.found-items'
                        : 'illustrations.report')
            </div>
        </div>
        <div class="catalog-tools">
            <form class="catalog-search" method="post" action="{{ route('laporan.search', $jenis) }}"
                role="search">
                @csrf
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="10" cy="10" r="6" stroke="currentColor"
                        stroke-width="1.7" />
                    <path d="m15 15 5 5" stroke="currentColor" stroke-width="1.7"
                        stroke-linecap="round" />
                </svg>
                <label class="sr-only" for="catalog-search">Cari deskripsi atau lokasi</label>
                <input id="catalog-search" name="q" value="{{ $q }}" maxlength="100"
                    placeholder="Nama barang atau lokasi" autocomplete="off">
                <button class="button accent" type="submit">Cari</button>
            </form>
            @error('q')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
            <div class="catalog-toolbar">
                <nav class="catalog-tabs" aria-label="Jenis laporan">
                    <a href="{{ route('laporan.index', 'ditemukan') }}"
                        @if ($jenis === 'ditemukan') aria-current="page" @endif>Barang
                        ditemukan</a>
                    <a href="{{ route('laporan.index', 'hilang') }}"
                        @if ($jenis === 'hilang') aria-current="page" @endif>Barang hilang</a>
                </nav>
                <p class="catalog-total"><strong>{{ $laporan->total() }}</strong>
                    {{ $q !== '' ? 'hasil pencarian' : 'laporan' }} <span
                        aria-hidden="true">&middot;</span> Terbaru dahulu</p>
            </div>
            @if ($q !== '')
                <div class="search-result">
                    <p>Hasil untuk <strong>&ldquo;{{ $q }}&rdquo;</strong></p>
                    <a class="text-link" href="{{ route('laporan.index', $jenis) }}">Hapus
                        pencarian</a>
                </div>
            @endif
        </div>
        <div class="catalog-grid" role="list" data-page-size="{{ $laporan->perPage() }}">
            @forelse ($laporan as $item)
                @include('partials.report-card', ['item' => $item])
            @empty
                <div class="catalog-empty" role="listitem">
                    <div aria-hidden="true">@include('illustrations.report')</div>
                    <h2>{{ $q !== '' ? 'Belum ketemu yang cocok' : 'Belum ada laporan di sini' }}</h2>
                    <p>{{ $q !== '' ? 'Coba nama barang yang lebih singkat atau lokasi lain di kampus' : 'Laporan yang sudah diperiksa petugas akan muncul di halaman ini' }}
                    </p>
                    <a class="button"
                        href="{{ $q !== '' ? route('laporan.index', $jenis) : route('laporan.choose') }}">{{ $q !== '' ? 'Lihat semua barang' : 'Buat laporan' }}</a>
                </div>
            @endforelse
        </div>
        @include('public.partials.pagination')
        <aside class="catalog-help">
            <div>
                <h2>{{ $jenis === 'ditemukan' ? 'Barangmu belum ada?' : 'Menemukan sesuatu?' }}</h2>
                <p>{{ $jenis === 'ditemukan' ? 'Tinggalkan laporan supaya petugas bisa bantu mencocokkan' : 'Satu laporanmu bisa jadi kabar baik untuk pemiliknya' }}
                </p>
            </div>
            <a class="button"
                href="{{ route('laporan.create', $jenis === 'ditemukan' ? 'hilang' : 'ditemukan') }}">{{ $jenis === 'ditemukan' ? 'Laporkan kehilangan' : 'Laporkan barang temuan' }}
                <span aria-hidden="true">&rarr;</span></a>
        </aside>
    </section>
@endsection
