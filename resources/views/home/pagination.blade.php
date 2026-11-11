@if ($laporan->hasPages())
    @php
        $currentPage = $laporan->currentPage();
        $indexRoute = $preview ? 'mockup' : 'home';
        $pageRoute = $preview ? 'mockup.page' : 'home.page';

        $pageUrl = static function (int $page) use ($indexRoute, $pageRoute): string {
            $url = $page === 1 ? route($indexRoute) : route($pageRoute, $page);

            return $url . '#laporan-terbaru';
        };
    @endphp

    <nav class="report-pagination" aria-label="Navigasi halaman barang temuan">
        @if ($currentPage > 1)
            <a href="{{ $pageUrl($currentPage - 1) }}">&larr; Sebelumnya</a>
        @endif

        <span>Halaman {{ $currentPage }} dari {{ $laporan->lastPage() }}</span>
        @if ($laporan->hasMorePages())
            <a href="{{ $pageUrl($currentPage + 1) }}">Berikutnya &rarr;</a>
        @endif
    </nav>
@endif
