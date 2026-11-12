@if ($laporan->adminStage() === 'dipublikasikan')
    <section class="card admin-action-card admin-unpublish" aria-labelledby="unpublish-title">
        <span class="eyebrow">PENGELOLAAN PUBLIKASI</span>
        <h2 class="admin-section-title" id="unpublish-title">Hapus publikasi</h2>
        <p>Laporan langsung hilang dari katalog. Data laporan tetap tersimpan dan bisa
            dipublikasikan lagi setelah diperiksa</p>
        <form method="post" action="{{ route('admin.unpublish', $laporan) }}">
            @csrf
            <label class="admin-unpublish-confirm">
                <input type="checkbox" name="konfirmasi" value="1" required>
                Saya yakin ingin menghapus publikasi {{ $laporan->kode_laporan }}
            </label>
            <button class="btn admin-unpublish-button" type="submit">Hapus publikasi</button>
        </form>
    </section>
@endif
