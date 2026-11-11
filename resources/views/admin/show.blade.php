@extends('layouts.admin')

@section('title', 'Detail laporan')

@section('content')
    @include('partials.back-link', [
        'href' => route('admin.index', [
            'jenis' => $laporan->jenis_laporan,
            'status' => $laporan->adminStage(),
        ]),
        'label' => 'Kembali ke daftar laporan',
    ])
    <div class="admin-detail-heading">
        <span
            class="eyebrow">{{ $laporan->jenis_laporan === 'hilang' ? 'LAPORAN BARANG HILANG' : 'LAPORAN BARANG DITEMUKAN' }}</span>
        <h1 class="page-title">{{ $laporan->kode_laporan }}</h1>
        <span class="status-badge"
            data-status="{{ $laporan->adminStage() }}">{{ ucfirst($laporan->adminStage()) }}</span>
    </div>
    <section class="card admin-report-overview" aria-label="Informasi laporan">
        <div class="admin-report-media">
            @include('partials.report-media', ['item' => $laporan])
            @if (count($laporan->foto_barang ?? []) > 1)
                <div class="admin-photos">
                    @foreach (array_slice($laporan->foto_barang, 1, null, true) as $i => $photo)
                        <a href="{{ route('foto.show', [$laporan, $i]) }}" target="_blank"
                            rel="noopener"><img class="admin-photo"
                                src="{{ route('foto.show', [$laporan, $i]) }}"
                                alt="Foto barang {{ $i + 1 }}"></a>
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <h2 class="admin-title">Informasi barang</h2>
            <p class="admin-description">{{ $laporan->deskripsi }}</p>
            <dl class="admin-facts">
                <div>
                    <dt>Jenis laporan</dt>
                    <dd>{{ ucfirst($laporan->jenis_laporan) }}</dd>
                </div>
                <div>
                    <dt>Pelapor / penemu</dt>
                    <dd>{{ $laporan->nama_pelapor }}</dd>
                </div>
                <div>
                    <dt>WhatsApp pelapor (privat)</dt>
                    <dd>
                        @php
                            $reporterChat = \App\Support\WhatsApp::url(
                                (string) $laporan->kontak_pelapor,
                                'Halo, kami petugas Temuin Aja ingin menghubungi Anda terkait laporan ' .
                                    $laporan->kode_laporan,
                            );
                        @endphp
                        @if ($reporterChat)
                            <a class="text-link" href="{{ $reporterChat }}" target="_blank"
                                rel="noopener noreferrer">{{ $laporan->kontak_pelapor }}</a>
                        @else
                            Belum dicantumkan
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Lokasi</dt>
                    <dd>{{ $laporan->lokasi }}</dd>
                </div>
                <div>
                    <dt>Tanggal kejadian</dt>
                    <dd>{{ $laporan->tanggal_kejadian->translatedFormat('d F Y') }}</dd>
                </div>
                @if ($laporan->dipublikasikan_pada)
                    <div>
                        <dt>Dipublikasikan</dt>
                        <dd>{{ $laporan->dipublikasikan_pada->translatedFormat('d F Y, H:i') }}</dd>
                    </div>
                @endif
                @if ($laporan->reviewer)
                    <div>
                        <dt>Diperiksa oleh</dt>
                        <dd>{{ $laporan->reviewer->nama_admin }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </section>
    <div class="admin-workflow">
        @include('admin.partials.review')
        @include('admin.partials.complete')
    </div>
    @include('admin.partials.completion-summary')
    @if ($laporan->adminStage() === 'ditolak')
        <div class="notice">Laporan
            ditolak{{ $laporan->catatan_admin ? ': ' . $laporan->catatan_admin : '' }}</div>
    @endif
    @if ($laporan->klaim->isNotEmpty() || $laporan->penyerahan->isNotEmpty())
        <details class="card admin-legacy-history">
            <summary>Riwayat proses sebelumnya</summary>
            <p>Catatan dari alur lama tetap tersimpan</p>
            @foreach ($laporan->klaim as $klaim)
                <p>Klaim {{ $klaim->nama_pengklaim }}: {{ ucfirst($klaim->status_klaim) }}</p>
            @endforeach
            @foreach ($laporan->penyerahan as $penyerahan)
                <p>Penyerahan {{ $penyerahan->tanggal_penyerahan->translatedFormat('d M Y, H:i') }}:
                    {{ $penyerahan->nama_penyerah }} ke {{ $penyerahan->nama_penerima }}</p>
            @endforeach
        </details>
    @endif
@endsection
