@extends('layouts.public')

@section('title', 'Riwayat laporan - Temuin Aja')

@section('content')
    <section class="page-container report-history-page" aria-labelledby="history-title">
        @include('partials.back-link', [
            'href' => route('laporan.choose'),
            'label' => 'Kembali ke laporan',
        ])
        <header class="history-heading">
            <span class="eyebrow">KABAR LAPORANMU</span>
            <h1 class="page-title" id="history-title">Riwayat <em>laporan</em></h1>
            <p class="page-intro">Pantau laporan yang kamu kirim, termasuk yang sudah selesai</p>
        </header>
        <details class="history-info">
            <summary><span>{{ $laporan->total() }} laporan tersimpan</span><span>Tentang riwayat</span>
            </summary>
            <p>Riwayat terhubung ke browser ini. Ganti browser, hapus cookie, atau tutup mode incognito
                akan menghilangkan aksesnya. Orang yang memakai browser yang sama juga bisa melihat
                riwayat ini</p>
            <p>Akses berlaku satu tahun sejak terakhir membuka riwayat atau membuat laporan</p>
        </details>
        <div class="history-list">
            @forelse ($laporan as $item)
                @php
                    $stage = $item->adminStage();
                    $statusCopy = match ($stage) {
                        'menunggu' => 'Laporan sedang menunggu pemeriksaan petugas',
                        'dipublikasikan' => 'Laporan sudah tampil di katalog barang',
                        'selesai' => $item->jenis_laporan === 'hilang'
                            ? 'Barang sudah ditemukan dan laporan telah selesai'
                            : 'Barang sudah diambil dan laporan telah selesai',
                        'ditolak'
                            => 'Laporan belum dapat dipublikasikan. Hubungi petugas dengan kode laporan untuk informasi lebih lanjut',
                    };
                @endphp
                <article class="surface history-item">
                    <div class="history-photo">
                        @if (!empty($item->foto_barang))
                            <img src="{{ route('laporan.history.photo', [$item, 0]) }}"
                                alt="Foto barang {{ $item->kode_laporan }}" loading="lazy"
                                decoding="async" width="480" height="360">
                        @else
                            <span>Foto belum tersedia</span>
                        @endif
                    </div>
                    <div class="history-item-heading">
                        <span
                            class="eyebrow">{{ $item->jenis_laporan === 'hilang' ? 'BARANG HILANG' : 'BARANG DITEMUKAN' }}</span>
                        <span class="status-badge"
                            data-status="{{ $stage }}">{{ ucfirst($stage) }}</span>
                    </div>
                    <h2>{{ Str::limit($item->deskripsi, 65) }}</h2>
                    <details class="history-details">
                        <summary><span class="history-detail-closed">Lihat detail</span><span
                                class="history-detail-open">Tutup detail</span></summary>
                        <p class="history-description">{{ $item->deskripsi }}</p>
                        <p class="history-status">{{ $statusCopy }}</p>
                        <dl class="history-facts">
                            <div>
                                <dt>Kode laporan</dt>
                                <dd><code>{{ $item->kode_laporan }}</code></dd>
                            </div>
                            <div>
                                <dt>Dikirim</dt>
                                <dd>{{ $item->created_at->translatedFormat('d M Y, H:i') }}</dd>
                            </div>
                            <div>
                                <dt>Lokasi kejadian</dt>
                                <dd>{{ $item->lokasi }}</dd>
                            </div>
                            <div>
                                <dt>Tanggal kejadian</dt>
                                <dd>{{ $item->tanggal_kejadian->translatedFormat('d M Y') }}</dd>
                            </div>
                        </dl>
                        @if (count($item->foto_barang ?? []) > 1)
                            <div class="history-extra-photos">
                                @foreach (array_slice($item->foto_barang, 1, null, true) as $index => $photo)
                                    <a href="{{ route('laporan.history.photo', [$item, $index]) }}"
                                        target="_blank" rel="noopener">Lihat foto
                                        {{ $index + 1 }}</a>
                                @endforeach
                            </div>
                        @endif
                    </details>
                </article>
            @empty
                <div class="surface history-empty">
                    <span class="eyebrow">MULAI CERITAMU</span>
                    <h2>Belum ada riwayat di sini</h2>
                    <p>Laporan baru yang dikirim dari browser ini akan muncul di sini. Laporan
                        sebelum
                        fitur riwayat tersedia belum tercatat</p>
                    <a class="button accent" href="{{ route('laporan.choose') }}">Buat
                        laporan</a>
                </div>
            @endforelse
        </div>
        @if ($laporan->hasPages())
            <nav class="catalog-pagination" aria-label="Halaman riwayat laporan">
                @if ($laporan->currentPage() > 1)
                    <a
                        href="{{ route('laporan.history.page', ['page' => $laporan->currentPage() - 1]) }}">&larr;
                        Sebelumnya</a>
                @endif
                <span>Halaman {{ $laporan->currentPage() }} dari
                    {{ $laporan->lastPage() }}</span>
                @if ($laporan->hasMorePages())
                    <a
                        href="{{ route('laporan.history.page', ['page' => $laporan->currentPage() + 1]) }}">Berikutnya
                        &rarr;</a>
                @endif
            </nav>
        @endif
    </section>
@endsection
