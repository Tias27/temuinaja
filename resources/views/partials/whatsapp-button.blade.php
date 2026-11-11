@php
    $whatsappUrl = \App\Support\WhatsApp::url(
        (string) config('temuin.whatsapp'),
        'Halo kak, saya ingin bertanya atau menyampaikan keluhan terkait Temuin Aja',
    );
@endphp
@if ($whatsappUrl)
    <a class="whatsapp-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
        aria-label="Chat lewat WhatsApp">
        <svg viewBox="0 0 32 32" fill="none" aria-hidden="true">
            <path d="M16 3.5a12.5 12.5 0 0 0-10.7 19L3.5 28.5l6.2-1.7A12.5 12.5 0 1 0 16 3.5Z"
                stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
            <path
                d="M11.3 9.7c-.4-.8-.9-.8-1.3-.8s-.9.1-1.4.6c-.5.6-1.8 1.8-1.8 4.3s1.9 4.9 2.2 5.3c.3.3 3.7 5.8 9.1 7.4 4.4 1.3 5.4 1 6.4.9 1-.2 3.1-1.3 3.6-2.5.4-1.1.4-2.1.3-2.3-.1-.2-.5-.4-1-.6l-3.1-1.5c-.4-.2-.7-.3-1 .2l-1.5 1.8c-.3.4-.6.5-1 .3-.5-.2-2.1-.8-4-2.5-1.5-1.3-2.5-2.9-2.8-3.4-.3-.5 0-.7.2-1l.7-.8.5-.8c.2-.3.1-.6 0-.8l-1.4-3.3Z"
                transform="translate(3.5 2) scale(.78)" fill="currentColor" />
        </svg>
        <span>Chat WhatsApp</span>
    </a>
@endif
