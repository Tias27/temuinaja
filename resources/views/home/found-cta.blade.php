<aside class="found-cta" id="laporkan-temuan" aria-labelledby="found-cta-title">
    <div class="found-cta-copy">
        <span class="found-cta-eyebrow">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            </svg>HAL KECIL, BERARTI BESAR</span>
        <h2 id="found-cta-title">Nemu barang?<br>
            <em>Jadi kabar baiknya</em>
        </h2>
        <p>Buat kamu mungkin cuma dompet atau kunci. Buat pemiliknya, bisa jadi yang paling dicari
            hari ini</p>
        <div class="found-cta-actions">
            <a class="found-cta-button" href="{{ route('laporan.create', 'ditemukan') }}">Laporkan
                barang temuan <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 18 18 6M6 6h12v12" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
            <a class="found-cta-secondary" href="{{ route('laporan.index', 'ditemukan') }}">Lagi
                cari barangmu?</a>
        </div>
    </div>
    <div class="found-cta-art" aria-hidden="true">
        @include('illustrations.found-items')

    </div>
</aside>
