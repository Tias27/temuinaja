<section class="concept-hero" aria-labelledby="home-title">
    <div class="mobile-hero-atmosphere" aria-hidden="true">
        <span class="hero-orbit hero-orbit-one"></span>
        <span class="hero-orbit hero-orbit-two"></span>
        <svg class="hero-drift hero-drift-search" viewBox="0 0 60 60" fill="none">
            <circle cx="25" cy="25" r="14" stroke="currentColor" stroke-width="2" />
            <path d="m36 36 13 13M20 19a8 8 0 0 1 7-3" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" />
        </svg>
    </div>
    <div class="copy">
        <span class="overline">Temuin Aja · Biro Jodoh Antara Kamu dan Barangmu yang Hilang</span>
        <h1 id="home-title">Nggak Semua yang Hilang Harus Jadi<br>
            <span>Kenangan<br>Yuk Temuin Aja</span>
        </h1>
        <p>Dompet, kartu, atau barang berharga yang kalau beli lagi harus rela makan mi instan
            seminggu. Intip temuan terbaru di lingkungan kampus, atau pasang laporan kehilanganmu
            sekarang juga.</p>
        <div class="hero-actions">
            <a class="button-primary" href="{{ route('laporan.index', 'ditemukan') }}">Cari
                barangmu <span aria-hidden="true">↗</span>
            </a>
            <a class="text-link" href="{{ route('laporan.create', 'ditemukan') }}">Saya menemukan
                barang</a>
        </div>
    </div>
    <div class="scene" aria-label="Ilustrasi contoh daftar barang ditemukan">
        <div class="orbit" aria-hidden="true"></div>
        <div class="float-card one" aria-hidden="true">
            <span class="float-icon">✓</span>
            <span>
                <strong>Sudah diperiksa</strong>
                <small>Oleh petugas
                    akademik</small>
            </span>
        </div>
        <div class="phone" aria-hidden="true">
            <div class="screen">
                <div class="notch"></div>
                <div class="screen-brand">Temuin <b>Aja</b>
                </div>
                <h2 class="screen-title">Yang ditemukan<br>hari ini.</h2>
                <p class="screen-description">Mungkin barangmu ada di sini.</p>
                <div class="screen-search">⌕ &nbsp; Cari barang atau lokasi</div>
                <div class="screen-tabs">
                    <span>Ditemukan</span>
                    <span>Hilang</span>
                </div>
                <div class="screen-card">
                    <div class="item-art">
                        <svg viewBox="0 0 100 100" fill="none" aria-hidden="true">
                            <path
                                d="M28 31h44a7 7 0 0 1 7 7v35a7 7 0 0 1-7 7H28a7 7 0 0 1-7-7V38a7 7 0 0 1 7-7Z"
                                fill="#C76545" />
                            <path d="M35 32v-7a15 15 0 0 1 30 0v7" stroke="#A94F34"
                                stroke-width="7" />
                            <path d="M21 48h58v13H21" fill="#A94F34" />
                            <circle cx="50" cy="54" r="3" fill="#FFFAF4" />
                        </svg>
                    </div>
                    <div class="screen-card-body">
                        <span class="screen-badge">Tersedia di
                            Akademik</span>
                        <strong>Tas kecil warna cokelat</strong>
                        <small>Gedung B ·
                            24 Okt 2026</small>
                    </div>
                </div>
                <div class="screen-bottom">
                    <b>⌂</b>
                    <span>⌕</span>
                    <span>▤</span>
                    <span>○</span>
                </div>
            </div>
        </div>
        <div class="float-card two" aria-hidden="true">
            <span class="float-icon">⌕</span>
            <span>
                <strong>Temukan kembali</strong>
                <small>Lewat
                    laporan kampus</small>
            </span>
        </div>
    </div>
</section>
