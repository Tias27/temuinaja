<article class="report-card" role="listitem">
    <a class="report-card-link" href="{{ route('laporan.show', $item) }}"
        aria-labelledby="report-title-{{ $item->id_laporan }}">
        @include('partials.report-media', ['item' => $item])
        <div class="report-body">
            <span
                class="report-status {{ $item->status_laporan === 'selesai' ? 'is-complete' : '' }}">
                <span class="report-status-full">@include('partials.report-status', ['item' => $item])</span>
                <span class="report-status-short" aria-hidden="true">
                    {{ str_starts_with($item->kode_laporan, 'TA-SAMPLE-') ? 'Contoh' : ($item->status_laporan === 'selesai' ? 'Selesai' : ($item->jenis_laporan === 'hilang' ? 'Dicari' : 'Temuan')) }}
                </span>
            </span>
            <h3 id="report-title-{{ $item->id_laporan }}">
                {{ Str::limit($item->deskripsi, 80) }}</h3>
            <p class="report-location" title="{{ $item->lokasi }}">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M16 8c0 4-6 9-6 9S4 12 4 8a6 6 0 1 1 12 0Z" stroke="currentColor"
                        stroke-width="1.4" />
                    <circle cx="10" cy="8" r="2" stroke="currentColor"
                        stroke-width="1.4" />
                </svg>
                <span>{{ $item->lokasi }}</span>
            </p>
            <div class="report-card-footer">
                <time
                    datetime="{{ $item->tanggal_kejadian->format('Y-m-d') }}">{{ $item->tanggal_kejadian->translatedFormat('d M Y') }}</time>
                <span class="report-detail"><span class="report-detail-label">Lihat detail</span>
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
        </div>
    </a>
</article>
