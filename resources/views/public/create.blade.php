@extends('layouts.public')

@section('title')
    {{ $jenis === 'hilang' ? 'Laporkan kehilangan' : 'Laporkan barang temuan' }} - Temuin Aja
@endsection

@section('content')
    <section class="page-container report-form-page">
        @include('partials.back-link', [
            'href' => route('laporan.choose'),
            'label' => 'Pilih jenis laporan',
        ])
        <div class="report-form-layout">
            <aside class="report-form-intro">
                <span class="eyebrow">BUAT LAPORAN</span>
                <h1 class="page-title">
                    {{ $jenis === 'hilang' ? 'Biar kami bantu' : 'Jadi kabar baik' }}<br><em>{{ $jenis === 'hilang' ? 'cari kabarnya' : 'untuk pemiliknya' }}</em>
                </h1>
                <p class="page-intro">
                    {{ $jenis === 'hilang' ? 'Ceritakan barang yang hilang. Detail yang jelas membantu petugas mencocokkan dengan barang temuan' : 'Menemukan barang di kampus? Isi detailnya, lalu titipkan barang ke petugas akademik' }}
                </p>
                <div class="form-illustration" aria-hidden="true">
                    @include($jenis === 'hilang' ? 'illustrations.report' : 'illustrations.return')
                </div>
                <div class="form-guidance">
                    <h2>Sebelum dikirim</h2>
                    <ul>
                        <li>Gunakan foto yang jelas dan mudah dikenali</li>
                        <li>Jangan cantumkan nomor identitas atau data sensitif pada deskripsi dan foto
                        </li>
                        <li>Laporan diperiksa petugas sebelum tampil</li>
                    </ul>
                </div>
            </aside>
            <form class="surface report-form" data-report-form method="post"
                enctype="multipart/form-data" action="{{ route('laporan.store', $jenis) }}">
                @csrf
                <div class="report-type-field">
                    <p id="report-type-label">Jenis laporan</p>
                    <nav class="report-type-switch" aria-labelledby="report-type-label">
                        <a href="{{ route('laporan.create', 'hilang') }}"
                            @if ($jenis === 'hilang') aria-current="page" @endif>Kehilangan
                            barang</a>
                        <a href="{{ route('laporan.create', 'ditemukan') }}"
                            @if ($jenis === 'ditemukan') aria-current="page" @endif>Menemukan
                            barang</a>
                    </nav>
                </div>
                <div class="form-heading">
                    <span
                        class="eyebrow">{{ $jenis === 'hilang' ? 'LAPORAN KEHILANGAN' : 'LAPORAN BARANG TEMUAN' }}</span>
                    <h2>Lengkapi ceritanya</h2>
                    <p>Semua kolom perlu diisi</p>
                </div>
                @if ($errors->any())
                    <div class="notice error" role="alert">Laporan belum terkirim, periksa kembali
                        data berikut</div>
                @endif
                <div class="field">
                    <label
                        for="nama-pelapor">{{ $jenis === 'hilang' ? 'Nama pelapor' : 'Nama penemu' }}</label>
                    <input id="nama-pelapor" name="nama_pelapor" value="{{ old('nama_pelapor') }}"
                        required maxlength="120" autocomplete="name" placeholder="Nama lengkapmu"
                        aria-describedby="nama-hint"
                        @error('nama_pelapor') aria-invalid="true" @enderror>
                    <p id="nama-hint" class="field-hint">Nama hanya digunakan petugas, tidak tampil di
                        katalog</p>
                    @error('nama_pelapor')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="kontak-pelapor">Nomor WhatsApp</label>
                    <input id="kontak-pelapor" type="tel" name="kontak_pelapor"
                        value="{{ old('kontak_pelapor') }}" required maxlength="30" autocomplete="tel"
                        inputmode="tel" placeholder="08xxxxxxxxxx"
                        aria-describedby="kontak-hint kontak-error"
                        @error('kontak_pelapor') aria-invalid="true" @enderror>
                    <p id="kontak-hint" class="field-hint">Gunakan nomor aktif agar petugas bisa
                        menghubungimu. Nomor ini hanya terlihat oleh petugas</p>
                    @error('kontak_pelapor')
                        <p id="kontak-error" class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field upload-field">
                    <label for="foto-barang">Foto barang</label>
                    <input id="foto-barang" type="file" name="foto_barang[]"
                        accept="image/jpeg,image/png,image/webp" required multiple
                        aria-describedby="foto-hint foto-error">
                    <p id="foto-hint" class="field-hint">JPG, PNG, atau WebP &middot; 1&ndash;3 foto
                        &middot; maksimal 5 MB dan 4096 &times; 4096 piksel per foto</p>
                    <p id="foto-error" class="field-error" data-upload-error role="alert">
                        @error('foto_barang')
                            {{ $message }}
                            @enderror @error('foto_barang.*')
                            {{ $message }}
                        @enderror
                    </p>
                    <div class="upload-previews" data-upload-previews aria-live="polite"></div>
                    @if ($errors->any())
                        <p class="field-hint">Pilih ulang foto sebelum mengirim kembali</p>
                    @endif
                </div>
                <div class="field">
                    <label for="deskripsi">Deskripsi barang</label>
                    <textarea id="deskripsi" name="deskripsi" required minlength="10" maxlength="2000"
                        placeholder="Contoh: dompet lipat cokelat, ada goresan kecil di bagian depan"
                        @error('deskripsi') aria-invalid="true" @enderror>{{ old('deskripsi') }}</textarea>
                    <p class="field-hint">Sebutkan jenis, warna, merek, dan ciri yang mudah dikenali</p>
                    @error('deskripsi')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-row">
                    <div class="field">
                        <label
                            for="lokasi">{{ $jenis === 'hilang' ? 'Lokasi terakhir terlihat' : 'Lokasi ditemukan' }}</label>
                        <input id="lokasi" name="lokasi" value="{{ old('lokasi') }}" required
                            maxlength="180" placeholder="Contoh: kantin Gedung A"
                            @error('lokasi') aria-invalid="true" @enderror>
                        @error('lokasi')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field">
                        <label
                            for="tanggal-kejadian">{{ $jenis === 'hilang' ? 'Tanggal kehilangan' : 'Tanggal ditemukan' }}</label>
                        <input id="tanggal-kejadian" type="date" name="tanggal_kejadian"
                            max="{{ now()->toDateString() }}" value="{{ old('tanggal_kejadian') }}"
                            required @error('tanggal_kejadian') aria-invalid="true" @enderror>
                        @error('tanggal_kejadian')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="form-submit">
                    <p>Sudah sesuai? Kirim untuk diperiksa petugas</p>
                    <button class="button accent" type="submit">Kirim laporan <span
                            aria-hidden="true">&rarr;</span></button>
                </div>
            </form>
        </div>
    </section>
@endsection
