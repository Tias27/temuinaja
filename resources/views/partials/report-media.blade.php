@php
    $sampleArtwork = match ($item->kode_laporan) {
        'TA-SAMPLE-CHARGER' => 'charger',
        'TA-SAMPLE-KACAMATA' => 'glasses',
        'TA-SAMPLE-PAYUNG' => 'umbrella',
        'TA-SAMPLE-EARBUDS' => 'earbuds',
        'TA-SAMPLE-DOMPET' => 'wallet',
        'TA-SAMPLE-TUMBLER' => 'bottle',
        'TA-SAMPLE-KUNCI' => 'keys',
        'TA-SAMPLE-KTM' => 'id-card',
        default => null,
    };
@endphp
<div class="report-media"
    @if ($sampleArtwork) data-artwork="{{ $sampleArtwork }}" @endif>
    @if ($sampleArtwork)
        @include('illustrations.sample-item', [
            'artwork' => $sampleArtwork,
        ])
        <span class="report-image-note">Ilustrasi contoh</span>
    @else
        <img src="{{ route('foto.show', [$item, 0]) }}?v={{ $item->updated_at->timestamp }}"
            alt="{{ $item->deskripsi }}" loading="lazy" decoding="async" width="600" height="400">
    @endif
</div>
