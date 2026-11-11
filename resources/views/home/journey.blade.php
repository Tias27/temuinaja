<section class="home-section journey" id="cara-kerja" aria-labelledby="cara-title">
    <div class="journey-heading">
        <div>
            <span class="overline">CARA KERJA</span>
            <h2 id="cara-title">Hilang, ketemu,<br>
                <em>balik ke kamu</em>
            </h2>
        </div>
        <div class="journey-intro">
            <span class="journey-pill">Tiga langkah, lebih tenang</span>
            <p>Dari kabar pertama sampai barang kembali ke tangan yang tepat. Begini alurnya.</p>
        </div>
    </div>
    <ol class="journey-steps" role="list">
        <li class="journey-step">
            <div class="journey-art" aria-hidden="true">
                @include('illustrations.report')

            </div>
            <div class="journey-copy">
                <div class="journey-marker">
                    <span class="journey-number" aria-hidden="true">01</span>
                    <span class="journey-kicker">Mulai dari
                        kamu</span>
                </div>
                <h3>Cari atau laporkan</h3>
                <p>Cek barang temuan. Belum ketemu? Kirim laporan dengan foto dan ciri barangmu</p>
            </div>
        </li>
        <li class="journey-step">
            <div class="journey-art" aria-hidden="true">
                @include('illustrations.review')

            </div>
            <div class="journey-copy">
                <div class="journey-marker">
                    <span class="journey-number" aria-hidden="true">02</span>
                    <span class="journey-kicker">Kami bantu
                        cek</span>
                </div>
                <h3>Petugas memeriksa</h3>
                <p>Laporan diperiksa sebelum terbit. Barang temuan dititipkan di akademik agar aman.
                </p>
            </div>
        </li>
        <li class="journey-step">
            <div class="journey-art" aria-hidden="true">
                @include('illustrations.return')

            </div>
            <div class="journey-copy">
                <div class="journey-marker">
                    <span class="journey-number" aria-hidden="true">03</span>
                    <span class="journey-kicker">Ketemu lagi,
                        deh</span>
                </div>
                <h3>Buktikan, lalu bawa pulang</h3>
                <p>Merasa itu barangmu? Hubungi petugas dan tunjukkan bukti kepemilikan sebelum
                    mengambilnya.</p>
            </div>
        </li>
    </ol>
    <div class="journey-footer">
        <p class="journey-reassurance"><svg viewBox="0 0 24 26" fill="none" aria-hidden="true">
                <path d="m12 2 8 3v7c0 6-8 11-8 11S4 18 4 12V5l8-3Z" stroke="currentColor"
                    stroke-width="1.5" />
                <path d="m8 12 3 3 5-6" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round" />
            </svg>Kepemilikan dicek sebelum barang diserahkan
        </p>
        <a class="journey-link" href="{{ route('laporan.index', 'ditemukan') }}">Mulai cari
            barangmu <span aria-hidden="true">&rarr;</span>
        </a>
    </div>
</section>
