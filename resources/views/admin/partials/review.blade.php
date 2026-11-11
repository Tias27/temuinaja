@if ($laporan->adminStage() === 'menunggu')
    <section class="card admin-action-card" aria-labelledby="review-title">
        <span class="eyebrow">LANGKAH 1 DARI 2</span>
        <h2 class="admin-section-title" id="review-title">Periksa lalu publikasikan</h2>
        <p>Pastikan foto dan keterangan sesuai. Setelah dipublikasikan, laporan langsung muncul di
            katalog barang</p>
        <form method="post" action="{{ route('admin.review', $laporan) }}">
            @csrf
            <label>Catatan petugas (opsional)
                <textarea name="catatan_admin" maxlength="1000">{{ old('catatan_admin', $laporan->catatan_admin) }}</textarea>
            </label>
            <div class="admin-review-actions">
                <button name="aksi" value="terima" class="btn btn-acc"
                    type="submit">Publikasikan laporan</button>
                <button name="aksi" value="tolak" class="btn" type="submit">Tolak
                    laporan</button>
            </div>
        </form>
    </section>
@endif
