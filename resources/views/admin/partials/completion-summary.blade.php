@if ($laporan->adminStage() === 'selesai')
    @php
        $completion = $laporan->penyelesaian;
        $legacyHandover = $laporan->penyerahan
            ->where('jenis_penyerahan', 'ke_pemilik')
            ->sortByDesc('tanggal_penyerahan')
            ->first();
        $isLost = $laporan->jenis_laporan === 'hilang';
    @endphp
    <section class="card admin-completion" aria-labelledby="completion-title">
        <span class="eyebrow">RIWAYAT PENYELESAIAN</span>
        <h2 class="admin-section-title" id="completion-title">
            {{ $isLost ? 'Barang ditemukan' : 'Barang diambil' }}</h2>
        <p class="admin-completion-intro">Laporan selesai dan tersimpan sebagai catatan petugas</p>
        <dl class="admin-completion-facts">
            <div>
                <dt>Kode laporan</dt>
                <dd><code>{{ $laporan->kode_laporan }}</code></dd>
            </div>
            <div>
                <dt>Barang</dt>
                <dd>{{ $laporan->deskripsi }}</dd>
            </div>
            @if ($completion)
                <div>
                    <dt>{{ $isLost ? 'Penemu' : 'Penerima' }}</dt>
                    <dd>{{ $completion->nama_pihak }}</dd>
                </div>
                <div>
                    <dt>WhatsApp {{ $isLost ? 'penemu' : 'penerima' }}</dt>
                    <dd>{{ $completion->kontak_pihak }}</dd>
                </div>
                @if ($isLost)
                    <div>
                        <dt>Lokasi ditemukan</dt>
                        <dd>{{ $completion->lokasi_ditemukan }}</dd>
                    </div>
                @else
                    <div>
                        <dt>Catatan verifikasi</dt>
                        <dd>{{ $completion->catatan_verifikasi }}</dd>
                    </div>
                @endif
                <div>
                    <dt>Diselesaikan oleh</dt>
                    <dd>{{ $completion->admin->nama_admin }}</dd>
                </div>
                <div>
                    <dt>Tanggal penyelesaian</dt>
                    <dd><time
                            datetime="{{ $completion->diselesaikan_pada->toIso8601String() }}">{{ $completion->diselesaikan_pada->translatedFormat('d F Y, H:i') }}</time>
                    </dd>
                </div>
            @elseif ($legacyHandover)
                <div>
                    <dt>Penyerah</dt>
                    <dd>{{ $legacyHandover->nama_penyerah }}</dd>
                </div>
                <div>
                    <dt>Penerima</dt>
                    <dd>{{ $legacyHandover->nama_penerima }}</dd>
                </div>
                <div>
                    <dt>Diselesaikan oleh</dt>
                    <dd>{{ $legacyHandover->admin?->nama_admin ?? 'Petugas lama' }}</dd>
                </div>
                <div>
                    <dt>Tanggal penyerahan</dt>
                    <dd><time
                            datetime="{{ $legacyHandover->tanggal_penyerahan->toIso8601String() }}">{{ $legacyHandover->tanggal_penyerahan->translatedFormat('d F Y, H:i') }}</time>
                    </dd>
                </div>
            @else
                <div>
                    <dt>Catatan</dt>
                    <dd>Data penyelesaian alur lama belum tercatat lengkap</dd>
                </div>
            @endif
        </dl>
    </section>
@endif
