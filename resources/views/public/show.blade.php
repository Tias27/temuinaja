@extends('layouts.public')

@section('title', 'Detail barang - Temuin Aja')

@section('content')
    <section class="detail page-container">
        @include('partials.back-link', [
            'href' => route('laporan.index', $laporan->jenis_laporan),
        ])
        <div class="detail-grid">
            <div class="detail-gallery">
                @include('partials.report-media', ['item' => $laporan])
                @if (count($laporan->foto_barang ?? []) > 1)
                    <div class="detail-extra-photos">
                        @foreach (array_slice($laporan->foto_barang, 1, null, true) as $index => $photo)
                            <a href="{{ route('foto.show', [$laporan, $index]) }}" target="_blank"
                                rel="noopener" aria-label="Buka foto barang {{ $index + 1 }}">
                                <img src="{{ route('foto.show', [$laporan, $index]) }}"
                                    alt="Foto barang dari sisi lain" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                @endif
                <p class="gallery-caption">
                    {{ str_starts_with($laporan->kode_laporan, 'TA-SAMPLE-') ? 'Ilustrasi untuk data contoh' : 'Cocokkan foto dan ciri barang sebelum menghubungi petugas' }}
                </p>
            </div>
            <div class="detail-copy">
                <span
                    class="report-status {{ $laporan->status_laporan === 'selesai' ? 'is-complete' : '' }}">
                    @include('partials.report-status', ['item' => $laporan])
                </span>
                <h1 class="page-title">{{ Str::limit($laporan->deskripsi, 100) }}</h1>
                <dl class="detail-facts">
                    <div>
                        <dt>{{ $laporan->jenis_laporan === 'hilang' ? 'Terakhir terlihat' : 'Ditemukan di' }}
                        </dt>
                        <dd>{{ $laporan->lokasi }}</dd>
                    </div>
                    <div>
                        <dt>{{ $laporan->jenis_laporan === 'hilang' ? 'Tanggal kehilangan' : 'Tanggal ditemukan' }}
                        </dt>
                        <dd>{{ $laporan->tanggal_kejadian->translatedFormat('d F Y') }}</dd>
                    </div>
                </dl>
                <div class="detail-description">
                    <h2>Ciri barang</h2>
                    <p>{{ $laporan->deskripsi }}</p>
                </div>
                <aside class="detail-contact">
                    @if (str_starts_with($laporan->kode_laporan, 'TA-SAMPLE-'))
                        <span class="eyebrow">DATA CONTOH</span>
                        <h2>Gambaran cara kerjanya</h2>
                        <p>Barang ini hanya ilustrasi katalog, bukan barang yang bisa diklaim</p>
                    @elseif ($laporan->status_laporan === 'selesai')
                        <span class="eyebrow">LAPORAN SELESAI</span>
                        <h2>{{ $laporan->jenis_laporan === 'hilang' ? 'Barang sudah ditemukan' : 'Barang sudah diambil' }}
                        </h2>
                        <p>Petugas sudah mencatat penyelesaian laporan ini. Laporan tetap ditampilkan
                            selama 2 &times; 24 jam setelah selesai, lalu otomatis disembunyikan</p>
                    @else
                        @php
                            $isLost = $laporan->jenis_laporan === 'hilang';
                            $reportMessage = $isLost
                                ? 'Halo kak, saya menemukan barang yang sepertinya sesuai dengan laporan ' .
                                    $laporan->kode_laporan .
                                    '. Mohon bantu mencocokkan dengan pemiliknya'
                                : 'Halo kak, saya merasa barang pada laporan ' .
                                    $laporan->kode_laporan .
                                    ' ini milik saya. Bagaimana proses verifikasi kepemilikannya?';
                            $reportChat = \App\Support\WhatsApp::url(
                                (string) config('temuin.whatsapp'),
                                $reportMessage . "\n\n" . route('laporan.show', $laporan),
                            );
                        @endphp
                        <span class="eyebrow">LANGKAH BERIKUTNYA</span>
                        <h2>{{ $laporan->jenis_laporan === 'hilang' ? 'Punya kabar tentang barang ini?' : 'Merasa ini barangmu?' }}
                        </h2>
                        <p>{{ $laporan->jenis_laporan === 'hilang' ? 'Sampaikan informasi yang kamu punya kepada petugas akademik' : 'Siapkan bukti kepemilikan dan hubungi petugas akademik untuk mencocokkan barang' }}
                        </p>
                        <p class="detail-report-code">Kode laporan
                            <strong>{{ $laporan->kode_laporan }}</strong>
                        </p>
                        @if ($reportChat)
                            <a class="button accent detail-contact-action" href="{{ $reportChat }}"
                                target="_blank" rel="noopener noreferrer">
                                {{ $isLost ? 'Saya menemukan barang ini' : 'Ini sepertinya barang saya' }}
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                            <p class="detail-chat-hint">Chat petugas lewat WhatsApp, kode dan tautan
                                laporan sudah disertakan</p>
                        @endif
                        <ol class="detail-next-steps" aria-label="Proses penanganan barang">
                            <li>{{ $isLost ? 'Sampaikan ciri barang dan lokasi kamu menemukannya' : 'Ceritakan ciri khusus atau siapkan bukti kepemilikan' }}
                            </li>
                            <li>{{ $isLost ? 'Petugas mencocokkan barang dan menghubungi pelapor' : 'Petugas memeriksa bukti sebelum menyetujui klaim' }}
                            </li>
                            <li>Penyerahan diatur bersama petugas, lalu laporan ditandai selesai</li>
                        </ol>
                        <dl class="contact-details">
                            <div>
                                <dt>Kontak</dt>
                                <dd>{{ config('temuin.contact') }}</dd>
                            </div>
                            <div>
                                <dt>Lokasi</dt>
                                <dd>{{ config('temuin.address') }}</dd>
                            </div>
                            <div>
                                <dt>Jam layanan</dt>
                                <dd>{{ config('temuin.hours') }}</dd>
                            </div>
                        </dl>
                    @endif
                </aside>
            </div>
        </div>
    </section>
@endsection
