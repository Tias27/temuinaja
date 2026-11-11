@if ($laporan->hasPages())
    @php
        $currentPage = $laporan->currentPage();

        $pageUrl = static function (int $page) use ($jenis, $term): string {
            if ($term) {
                return route('laporan.search.index', [
                    'jenis' => $jenis,
                    'term' => $term,
                    'page' => $page,
                ]);
            }

            return $page === 1
                ? route('laporan.index', $jenis)
                : route('laporan.page', ['jenis' => $jenis, 'page' => $page]);
        };
    @endphp

    <nav class="catalog-pagination" aria-label="Navigasi halaman laporan">
        @if ($currentPage > 1)
            <a href="{{ $pageUrl($currentPage - 1) }}">&larr; Sebelumnya</a>
        @endif

        <span>Halaman {{ $currentPage }} dari {{ $laporan->lastPage() }}</span>
        @if ($laporan->hasMorePages())
            <a href="{{ $pageUrl($currentPage + 1) }}">Berikutnya &rarr;</a>
        @endif
    </nav>
@endif
