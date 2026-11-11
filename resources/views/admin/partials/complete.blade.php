@if (
    $laporan->adminStage() === 'dipublikasikan' &&
        !str_starts_with($laporan->kode_laporan, 'TA-SAMPLE-'))
    @php
        $isLost = $laporan->jenis_laporan === 'hilang';
    @endphp
    <section class="card admin-action-card" aria-labelledby="complete-title">
        <span class="eyebrow">LANGKAH 2 DARI 2</span>
        <h2 class="admin-section-title" id="complete-title">
            {{ $isLost ? 'Barang sudah ditemukan?' : 'Barang sudah diambil?' }}</h2>
        <p>{{ $isLost ? 'Catat penemu dan lokasi barang ditemukan' : 'Catat penerima setelah bukti kepemilikan diperiksa' }}
        </p>
        <form method="post" action="{{ route('admin.complete', $laporan) }}">
            @csrf
            <label for="nama-pihak">{{ $isLost ? 'Nama penemu' : 'Nama penerima' }}
                <input id="nama-pihak" name="nama_pihak" value="{{ old('nama_pihak') }}"
                    maxlength="120" autocomplete="off" required>
                @error('nama_pihak')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>
            <label for="kontak-pihak">WhatsApp {{ $isLost ? 'penemu' : 'penerima' }}
                <input id="kontak-pihak" type="tel" name="kontak_pihak"
                    value="{{ old('kontak_pihak') }}" maxlength="30" inputmode="tel"
                    placeholder="08xxxxxxxxxx" required>
                @error('kontak_pihak')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>
            @if ($isLost)
                <label for="lokasi-ditemukan">Lokasi barang ditemukan
                    <input id="lokasi-ditemukan" name="lokasi_ditemukan"
                        value="{{ old('lokasi_ditemukan') }}" maxlength="180" required>
                    @error('lokasi_ditemukan')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
            @else
                <label for="catatan-verifikasi">Catatan verifikasi kepemilikan
                    <textarea id="catatan-verifikasi" name="catatan_verifikasi" maxlength="1000" required
                        placeholder="Contoh: ciri khusus dan bukti yang ditunjukkan penerima">{{ old('catatan_verifikasi') }}</textarea>
                    @error('catatan_verifikasi')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
            @endif
            <p class="admin-action-note">Petugas penyelesai dan tanggal dicatat otomatis. Data pihak
                terkait hanya terlihat oleh admin</p>
            <button class="btn btn-acc admin-submit" type="submit">Selesaikan laporan <span
                    aria-hidden="true">&rarr;</span></button>
        </form>
    </section>
@endif
