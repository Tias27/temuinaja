@extends('layouts.public')

@section('title', 'Riwayat laporan - Temuin Aja')

@section('content')
    <section class="page-container report-history-page" aria-labelledby="history-title">
        @include('partials.back-link', [
            'href' => route('laporan.choose'),
            'label' => 'Kembali ke laporan',
        ])
        <span class="eyebrow">KABAR LAPORANMU</span>
        <h1 class="page-title" id="history-title">Riwayat <em>laporan</em></h1>
        <p class="page-intro">Semua laporan yang kamu kirim dari browser ini, termasuk yang sudah selesai
        </p>
        <div class="notice history-privacy">
            Akses riwayat tersimpan di browser ini. Ganti browser, hapus cookie, atau tutup mode
            incognito akan menghilangkan aksesnya. Orang yang memakai browser yang sama juga bisa
            melihat riwayat ini
        </div>
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
                    <div class="history-item-heading">
                        <span
                            class="eyebrow">{{ $item->jenis_laporan === 'hilang' ? 'BARANG HILANG' : 'BARANG DITEMUKAN' }}</span>
                        <span class="status-badge"
                            data-status="{{ $stage }}">{{ ucfirst($stage) }}</span>
                    </div>
                    <h2>{{ $item->deskripsi }}</h2>
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
                </article>
            @empty
                <div class="surface history-empty">
                    <span class="eyebrow">MULAI CERITAMU</span>
                    <h2>Belum ada riwayat di sini</h2>
                    <p>Laporan baru yang dikirim dari browser ini akan muncul di sini. Laporan sebelum
                        fitur riwayat tersedia belum tercatat</p>
                    <a class="button accent" href="{{ route('laporan.choose') }}">Buat laporan</a>
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
                <span>Halaman {{ $laporan->currentPage() }} dari {{ $laporan->lastPage() }}</span>
                @if ($laporan->hasMorePages())
                    <a
                        href="{{ route('laporan.history.page', ['page' => $laporan->currentPage() + 1]) }}">Berikutnya
                        &rarr;</a>
                @endif
            </nav>
        @endif
        <p class="history-footnote">Akses diperpanjang setiap kali kamu membuka riwayat atau membuat
            laporan, dan berakhir setelah satu tahun tidak digunakan</p>
    </section>
@endsection
