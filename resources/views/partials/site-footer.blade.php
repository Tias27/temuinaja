<footer class="mock-footer">
    <a class="footer-brand" href="{{ route('home') }}">Temuin Aja</a>
    <span>{{ $preview ?? false ? 'Pratinjau konsep - Ilustrasi ponsel hanya contoh' : 'Layanan barang hilang dan ditemukan kampus' }}</span>
    @if ($preview ?? false)
        <a href="{{ route('home') }}">Kembali ke situs aktif</a>
    @endif
</footer>
