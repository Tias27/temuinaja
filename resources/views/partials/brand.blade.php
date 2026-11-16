<a class="brand{{ $footerBrand ?? false ? ' footer-brand' : '' }}"
    href="{{ $preview ?? false ? url('/mockup') : route('home') }}" aria-label="Temuin Aja - beranda">
    <span class="brand-mark" aria-hidden="true">
        <img src="{{ asset('favicon.svg') }}?v=temuin-2" alt="" width="38" height="38">
    </span>
    <span class="brand-name">Temuin <span>Aja</span></span>
</a>
