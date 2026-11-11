<section class="home-section reports" id="laporan-terbaru" aria-labelledby="laporan-title">
    <div class="reports-heading">
        <div>
            <span class="overline">LAPORAN TERBARU</span>
            <h2 id="laporan-title">Barangkali ini yang kamu cari</h2>
            <p>Barang temuan di sekitar kampus, siapa tahu salah satunya milikmu</p>
        </div>
        <a class="reports-browse" href="{{ route('laporan.index', 'ditemukan') }}">
            Lihat semua barang
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.6"
                    stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
    </div>
    <div class="report-grid" id="recent-reports" role="list">
        @forelse($laporan as $item)
            @include('partials.report-card', ['item' => $item])
        @empty
            <div class="empty-reports" role="listitem">
                <p>Belum ada barang temuan yang dipublikasikan</p>
                <a href="{{ route('laporan.index', 'hilang') }}">Lihat laporan kehilangan <span
                        aria-hidden="true">&rarr;</span></a>
            </div>
        @endforelse
    </div>
    @if ($laporan->isNotEmpty())
        <div class="reports-footer">
            <p><span class="reports-count">{{ $laporan->total() }}</span> laporan barang temuan</p>
            <div class="report-slider-controls" aria-label="Geser daftar barang" hidden>
                <span class="report-slider-range" data-report-range aria-live="polite"
                    aria-atomic="true"></span>
                <button class="report-slider-button" type="button" data-slide="prev"
                    aria-controls="recent-reports" aria-label="Lihat barang sebelumnya">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M19 12H5m6-6-6 6 6 6" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <button class="report-slider-button" type="button" data-slide="next"
                    aria-controls="recent-reports" aria-label="Lihat barang berikutnya">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @include('home.pagination')
</section>
