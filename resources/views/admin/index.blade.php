@extends('layouts.admin')

@section('title', 'Kelola laporan')

@section('content')
    <div class="admin-page-heading">
        <div>
            <span class="eyebrow">RUANG PETUGAS</span>
            <h1 class="page-title">Kelola laporan<br><em>tanpa ribet</em></h1>
            <p class="page-intro">Periksa laporan, publikasikan, lalu catat saat barang ditemukan atau
                diambil</p>
        </div>
        <div class="admin-count">
            <strong>{{ $laporan->total() }}</strong><span>laporan pada daftar ini</span>
        </div>
    </div>
    <section class="card admin-list" aria-labelledby="admin-list-title">
        <div class="admin-list-heading">
            <div>
                <span class="eyebrow">PILIH JENIS LAPORAN</span>
                <h2 class="admin-title" id="admin-list-title">
                    {{ $jenis === 'hilang' ? 'Barang hilang' : 'Barang ditemukan' }}</h2>
            </div>
            <span class="admin-muted">Terbaru dahulu</span>
        </div>
        <nav class="admin-type-tabs" aria-label="Jenis laporan">
            @foreach (['ditemukan' => 'Barang ditemukan', 'hilang' => 'Barang hilang'] as $type => $label)
                <a href="{{ route('admin.index', ['jenis' => $type, 'status' => $status]) }}"
                    @if ($jenis === $type) aria-current="page" @endif>
                    <span>{{ $label }}</span>
                    <small>{{ $counts[$type] ?? 0 }}</small>
                </a>
            @endforeach
        </nav>
        <nav class="admin-stage-tabs" aria-label="Tahap laporan">
            @foreach (['menunggu' => 'Menunggu', 'dipublikasikan' => 'Dipublikasikan', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'semua' => 'Semua'] as $stage => $label)
                <a href="{{ route('admin.index', ['jenis' => $jenis, 'status' => $stage]) }}"
                    @if ($status === $stage) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="admin-report-list">
            @forelse ($laporan as $row)
                @php
                    $stage = $row->adminStage();
                @endphp
                <article class="admin-report-row">
                    <div class="admin-report-row-main">
                        <div class="admin-report-row-meta">
                            <code>{{ $row->kode_laporan }}</code>
                            @if (str_starts_with($row->kode_laporan, 'TA-SAMPLE-'))
                                <span>Data contoh</span>
                            @endif
                        </div>
                        <h3>{{ Str::limit($row->deskripsi, 90) }}</h3>
                        <p>{{ $row->lokasi }} <span aria-hidden="true">&middot;</span>
                            {{ $row->tanggal_kejadian->translatedFormat('d M Y') }}</p>
                        @if ($stage === 'selesai' && $row->penyelesaian)
                            <p class="admin-report-row-finished">Diselesaikan
                                {{ $row->penyelesaian->diselesaikan_pada->translatedFormat('d M Y, H:i') }}
                                oleh {{ $row->penyelesaian->admin->nama_admin }}</p>
                        @endif
                    </div>
                    <span class="status-badge"
                        data-status="{{ $stage }}">{{ ucfirst($stage) }}</span>
                    <a class="admin-manage" href="{{ route('admin.show', $row) }}">
                        {{ match ($stage) {
                            'menunggu' => 'Periksa',
                            'dipublikasikan' => 'Tindak lanjuti',
                            default => 'Lihat detail',
                        } }}
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </article>
            @empty
                <div class="admin-list-empty">
                    <h3>Belum ada laporan di tahap ini</h3>
                    <p>Pilih tahap lain untuk melihat laporan
                        {{ $jenis === 'hilang' ? 'kehilangan' : 'barang temuan' }}</p>
                </div>
            @endforelse
        </div>
        @if ($laporan->hasPages())
            <nav class="catalog-pagination" aria-label="Halaman laporan admin">
                @if ($laporan->previousPageUrl())
                    <a href="{{ $laporan->previousPageUrl() }}">&larr; Sebelumnya</a>
                @endif
                <span>Halaman {{ $laporan->currentPage() }} dari {{ $laporan->lastPage() }}</span>
                @if ($laporan->nextPageUrl())
                    <a href="{{ $laporan->nextPageUrl() }}">Berikutnya &rarr;</a>
                @endif
            </nav>
        @endif
    </section>
@endsection
