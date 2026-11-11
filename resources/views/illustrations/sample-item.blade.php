<svg class="report-illustration" viewBox="0 0 300 200" fill="none" aria-hidden="true"
    focusable="false">
    <ellipse cx="150" cy="166" rx="69" ry="8" fill="currentColor"
        opacity=".07" />
    @switch($artwork)
        @case('charger')
            <path d="M184 120h22c35 0 39-57 10-57-24 0-22 30-2 30h24" stroke="#80776C" stroke-width="5"
                stroke-linecap="round" />
            <rect x="234" y="85" width="18" height="15" rx="4" fill="#54504A" />
            <path d="M251 89h7v7h-7" fill="#B6B0A5" />
            <g transform="rotate(-9 136 106)">
                <path d="M111 69V50m25 19V50" stroke="#A7A49C" stroke-width="7" stroke-linecap="round" />
                <rect x="85" y="65" width="102" height="88" rx="19" fill="#FFFCF6"
                    stroke="#C8BBAA" stroke-width="2" />
                <path d="M169 69v80" stroke="#E4DACC" stroke-width="2" />
                <rect x="122" y="91" width="26" height="7" rx="3.5" fill="#756B5E" />
                <text x="135" y="127" text-anchor="middle" fill="#9D7960" font-family="Georgia, serif"
                    font-size="18">65W</text>
            </g>
        @break

        @case('glasses')
            <g transform="rotate(-8 150 108)">
                <rect x="65" y="62" width="171" height="75" rx="31" fill="#A69B8B" />
                <path d="M76 92h149" stroke="#8B806F" stroke-width="2" />
                <path d="m73 116 10-38m137 38-8-38M135 125c10-8 20-8 30 0" stroke="#45433D" stroke-width="5"
                    stroke-linecap="round" />
                <rect x="72" y="108" width="65" height="43" rx="17" fill="#E6E7DD"
                    fill-opacity=".85" stroke="#45433D" stroke-width="5" />
                <rect x="163" y="108" width="65" height="43" rx="17" fill="#E6E7DD"
                    fill-opacity=".85" stroke="#45433D" stroke-width="5" />
                <path d="m86 120 16 19m76-19 16 19" stroke="#FFFCF6" stroke-width="4" stroke-linecap="round"
                    opacity=".8" />
            </g>
        @break

        @case('umbrella')
            <g transform="rotate(26 150 100)">
                <path d="M150 35v111c0 23 29 23 29 2" stroke="#9D7654" stroke-width="7"
                    stroke-linecap="round" />
                <path
                    d="M81 102c2-40 28-64 69-64s67 24 69 64c-14-12-30-12-45 0-16-12-32-12-48 0-16-12-31-12-45 0Z"
                    fill="#4D6274" />
                <path d="M150 38c-17 11-23 35-24 64m24-64c17 11 23 35 24 64" stroke="#91A0A9"
                    stroke-width="1.5" />
                <path d="M150 38v58" stroke="#91A0A9" stroke-width="1.5" />
            </g>
        @break

        @case('earbuds')
            <g transform="rotate(-8 150 114)">
                <rect x="90" y="90" width="120" height="69" rx="27" fill="#FFFCF7"
                    stroke="#C7C6BA" stroke-width="2" />
                <rect x="90" y="69" width="120" height="48" rx="24" fill="#F4F2E9"
                    stroke="#C7C6BA" stroke-width="2" />
                <path d="M104 111h92" stroke="#D8D7CC" stroke-width="2" />
                <circle cx="151" cy="135" r="3" fill="#89977B" />
            </g>
            <g fill="#FFFCF7" stroke="#BFC0B6" stroke-width="1.8">
                <path d="M111 77V52c-12 0-18-5-18-13 0-10 7-16 17-16s17 6 17 16v38a8 8 0 0 1-16 0Z" />
                <path d="M182 68V43c12 0 18-5 18-13 0-10-7-16-17-16s-17 6-17 16v38a8 8 0 0 0 16 0Z" />
            </g>
            <path d="M103 37h9m66-9h10" stroke="#6F746B" stroke-width="5" stroke-linecap="round" />
        @break

        @case('wallet')
            <g transform="rotate(-9 150 106)">
                <path d="M86 84V66a9 9 0 0 1 9-9h107v27" fill="#8A543E" />
                <path d="M100 66h91" stroke="#E4C5A7" stroke-width="5" />
                <rect x="79" y="76" width="140" height="83" rx="14" fill="#B97552"
                    stroke="#92583D" stroke-width="2" />
                <path d="M91 88v53a6 6 0 0 0 6 6h108" stroke="#DFAB88" stroke-width="1.5"
                    stroke-dasharray="3 4" />
                <rect x="172" y="102" width="52" height="30" rx="6" fill="#D49A70"
                    stroke="#92583D" stroke-width="2" />
                <circle cx="187" cy="117" r="4" fill="#87573C" />
            </g>
        @break

        @case('bottle')
            <g transform="rotate(12 150 103)">
                <rect x="123" y="31" width="54" height="20" rx="6" fill="#66675F" />
                <rect x="118" y="47" width="64" height="115" rx="18" fill="#414640" />
                <path d="M125 60h50" stroke="#777E71" stroke-width="2" />
                <path d="M129 74v64" stroke="#737A6D" stroke-width="4" stroke-linecap="round" />
                <rect x="136" y="91" width="28" height="34" rx="5" fill="#5D6657" />
                <path d="m143 108 6 6 10-13" stroke="#BFC8AD" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </g>
        @break

        @case('keys')
            <circle cx="148" cy="69" r="30" stroke="#868B83" stroke-width="7" />
            <circle cx="148" cy="69" r="30" stroke="#D9DAD0" stroke-width="2" />
            <g transform="rotate(25 137 98)">
                <circle cx="137" cy="100" r="18" fill="#D6B278" stroke="#A58756"
                    stroke-width="2" />
                <circle cx="137" cy="96" r="6" fill="#EEE9DB" />
                <path d="M131 116v49h12v-10h9v-11h-9v-28" fill="#D6B278" stroke="#A58756"
                    stroke-width="2" stroke-linejoin="round" />
            </g>
            <g transform="rotate(-14 174 121)">
                <path d="M173 87V74" stroke="#868B83" stroke-width="5" />
                <rect x="155" y="85" width="39" height="69" rx="13" fill="#575A52" />
                <rect x="164" y="100" width="21" height="17" rx="5" fill="#818578" />
                <circle cx="174" cy="135" r="4" fill="#BC886B" />
            </g>
        @break

        @case('id-card')
            <g transform="rotate(-8 150 106)">
                <rect x="73" y="57" width="154" height="102" rx="12" fill="#FFFCF7"
                    stroke="#C3CCC9" stroke-width="2" />
                <path d="M85 57h130a12 12 0 0 1 12 12v15H73V69a12 12 0 0 1 12-12Z" fill="#6B858C" />
                <rect x="88" y="97" width="38" height="45" rx="5" fill="#E3E9E3" />
                <circle cx="107" cy="111" r="8" fill="#9CAFA8" />
                <path d="M94 137v-3a13 13 0 0 1 26 0v3" fill="#9CAFA8" />
                <path d="M140 105h63m-63 12h48m-48 13h56" stroke="#C1CCC6" stroke-width="4"
                    stroke-linecap="round" />
                <text x="89" y="75" fill="#FFFCF7" font-family="Georgia, serif" font-size="11"
                    letter-spacing="2">KARTU MAHASISWA</text>
            </g>
        @break
    @endswitch
</svg>
