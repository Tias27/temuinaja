@extends('layouts.public')

@php
    $editing = isset($laporan);
@endphp

@section('title')
    {{ $editing ? 'Edit laporan' : ($jenis === 'hilang' ? 'Laporkan kehilangan' : 'Laporkan barang temuan') }}
    - Temuin Aja
@endsection

@section('content')
    <section class="page-container report-form-page">
        @include('partials.back-link', [
            'href' => route($editing ? 'laporan.history' : 'laporan.choose'),
            'label' => $editing ? 'Kembali ke riwayat' : 'Pilih jenis laporan',
        ])
        <div class="report-form-layout">
            <aside class="report-form-intro">
                <span class="eyebrow">{{ $editing ? 'EDIT LAPORAN' : 'BUAT LAPORAN' }}</span>
                <h1 class="page-title">
                    @if ($editing)
                        Perbarui <em>laporanmu</em>
                    @else
                        {{ $jenis === 'hilang' ? 'Biar kami bantu' : 'Jadi kabar baik' }}<br><em>{{ $jenis === 'hilang' ? 'cari kabarnya' : 'untuk pemiliknya' }}</em>
                    @endif
                </h1>
                <p class="page-intro">
                    {{ $editing ? 'Perubahan akan diperiksa ulang oleh petugas. Laporan disembunyikan dari katalog sampai disetujui kembali' : ($jenis === 'hilang' ? 'Ceritakan barang yang hilang. Detail yang jelas membantu petugas mencocokkan dengan barang temuan' : 'Menemukan barang di kampus? Isi detailnya, lalu titipkan barang ke petugas akademik') }}
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
                data-refresh-url="{{ $editing ? route('laporan.history.edit', $laporan) : route('laporan.create', $jenis) }}"
                enctype="multipart/form-data"
                action="{{ $editing ? route('laporan.history.update', $laporan) : route('laporan.store', $jenis) }}">
                @csrf
                @if ($editing)
                    @method('PATCH')
                @endif
                @unless ($editing)
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
                @endunless
                <div class="form-heading">
                    <span
                        class="eyebrow">{{ $jenis === 'hilang' ? 'LAPORAN KEHILANGAN' : 'LAPORAN BARANG TEMUAN' }}</span>
                    <h2>Lengkapi ceritanya</h2>
                    <p>{{ $editing ? 'Foto baru opsional, kolom lainnya perlu diisi' : 'Semua kolom perlu diisi' }}
                    </p>
                </div>
                <div class="notice error" role="alert" data-report-errors
                    @if (!$errors->any()) hidden @endif>Laporan belum terkirim, periksa
                    kembali data berikut</div>
                <div class="field">
                    <label
                        for="nama-pelapor">{{ $jenis === 'hilang' ? 'Nama pelapor' : 'Nama penemu' }}</label>
                    <input id="nama-pelapor" name="nama_pelapor"
                        value="{{ old('nama_pelapor', $laporan->nama_pelapor ?? '') }}" required
                        maxlength="120" autocomplete="name" placeholder="Nama lengkapmu"
                        aria-describedby="nama-hint"
                        @error('nama_pelapor') aria-invalid="true" @enderror>
                    <p id="nama-hint" class="field-hint">Nama hanya digunakan petugas, tidak tampil di
                        katalog</p>
                    <p class="field-error" data-field-error="nama_pelapor" id="nama-error"
                        @if (!$errors->has('nama_pelapor')) hidden @endif>
                        @error('nama_pelapor')
                            {{ $message }}
                        @enderror
                    </p>
                </div>
                <div class="field">
                    <label for="kontak-pelapor">Nomor WhatsApp</label>
                    <input id="kontak-pelapor" type="tel" name="kontak_pelapor"
                        value="{{ old('kontak_pelapor', $laporan->kontak_pelapor ?? '') }}" required
                        maxlength="15" autocomplete="tel" inputmode="numeric" pattern="[0-9]+"
                        data-phone-digits placeholder="08xxxxxxxxxx"
                        aria-describedby="kontak-hint kontak-error"
                        @error('kontak_pelapor') aria-invalid="true" @enderror>
                    <p id="kontak-hint" class="field-hint">Gunakan nomor aktif agar petugas bisa
                        menghubungimu. Nomor ini hanya terlihat oleh petugas</p>
                    <p id="kontak-error" class="field-error" data-field-error="kontak_pelapor"
                        @if (!$errors->has('kontak_pelapor')) hidden @endif>
                        @error('kontak_pelapor')
                            {{ $message }}
                        @enderror
                    </p>
                </div>
                <div class="field upload-field">
                    <label for="foto-barang">Foto barang</label>
                    @if ($editing && !empty($laporan->foto_barang))
                        <div class="upload-previews" data-existing-photos>
                            @foreach ($laporan->foto_barang as $index => $photo)
                                <figure><img
                                        src="{{ route('laporan.history.photo', [$laporan, $index]) }}"
                                        alt="Foto tersimpan {{ $index + 1 }}">
                                    <figcaption>Foto tersimpan {{ $index + 1 }}</figcaption>
                                </figure>
                            @endforeach
                        </div>
                        <p class="field-hint">Biarkan kosong untuk memakai foto lama. Pilihan foto baru
                            akan mengganti seluruh foto lama</p>
                    @endif
                    <input id="foto-barang" type="file" name="foto_barang[]"
                        accept="image/jpeg,image/png,image/webp"
                        @if (!$editing || empty($laporan->foto_barang)) required @endif multiple
                        aria-describedby="foto-hint foto-error">
                    <p id="foto-hint" class="field-hint">JPG, PNG, atau WebP &middot; 1&ndash;3 foto
                        &middot; maksimal 5 MB dan 4096 &times; 4096 piksel per foto</p>
                    <p id="foto-error" class="field-error" data-upload-error
                        data-field-error="foto_barang" role="alert">
                        @error('foto_barang')
                            {{ $message }}
                            @enderror @error('foto_barang.*')
                            {{ $message }}
                        @enderror
                    </p>
                    <div class="upload-previews" data-upload-previews aria-live="polite"></div>
                    @if ($errors->any() && !$editing)
                        <p class="field-hint">Pilih ulang foto sebelum mengirim kembali</p>
                    @endif
                </div>
                <div class="field">
                    <label for="deskripsi">Deskripsi barang</label>
                    <textarea id="deskripsi" name="deskripsi" required minlength="10" maxlength="2000"
                        placeholder="Contoh: dompet lipat cokelat, ada goresan kecil di bagian depan"
                        @error('deskripsi') aria-invalid="true" @enderror>{{ old('deskripsi', $laporan->deskripsi ?? '') }}</textarea>
                    <p class="field-hint">Sebutkan jenis, warna, merek, dan ciri yang mudah dikenali</p>
                    <p class="field-error" data-field-error="deskripsi" id="deskripsi-error"
                        @if (!$errors->has('deskripsi')) hidden @endif>
                        @error('deskripsi')
                            {{ $message }}
                        @enderror
                    </p>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label
                            for="lokasi">{{ $jenis === 'hilang' ? 'Lokasi terakhir terlihat' : 'Lokasi ditemukan' }}</label>
                        <input id="lokasi" name="lokasi"
                            value="{{ old('lokasi', $laporan->lokasi ?? '') }}" required
                            maxlength="180" placeholder="Contoh: kantin Gedung A"
                            @error('lokasi') aria-invalid="true" @enderror>
                        <p class="field-error" data-field-error="lokasi" id="lokasi-error"
                            @if (!$errors->has('lokasi')) hidden @endif>
                            @error('lokasi')
                                {{ $message }}
                            @enderror
                        </p>
                    </div>
                    <div class="field">
                        <label
                            for="tanggal-kejadian">{{ $jenis === 'hilang' ? 'Tanggal kehilangan' : 'Tanggal ditemukan' }}</label>
                        <input id="tanggal-kejadian" type="date" name="tanggal_kejadian"
                            max="{{ now()->toDateString() }}"
                            value="{{ old('tanggal_kejadian', isset($laporan) ? $laporan->tanggal_kejadian->toDateString() : '') }}"
                            required @error('tanggal_kejadian') aria-invalid="true" @enderror>
                        <p class="field-error" data-field-error="tanggal_kejadian" id="tanggal-error"
                            @if (!$errors->has('tanggal_kejadian')) hidden @endif>
                            @error('tanggal_kejadian')
                                {{ $message }}
                            @enderror
                        </p>
                    </div>
                </div>
                <div class="form-submit">
                    <p>Sudah sesuai? Kirim untuk diperiksa petugas</p>
                    <button class="button accent"
                        type="submit">{{ $editing ? 'Simpan perubahan' : 'Kirim laporan' }} <span
                            aria-hidden="true">&rarr;</span></button>
                </div>
            </form>
        </div>
    </section>
@endsection
