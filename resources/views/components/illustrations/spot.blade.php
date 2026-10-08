@props(['name', 'circle' => true])
{{--
    VM Newswire spot illustrations: one 80×80 drawing per topic in a single style — navy outlines, teal primary
    fills, light-blue secondary fills, small amber highlights — optionally on a light teal disc.
--}}
@php
    $navy = '#10233F';
    $teal = '#18B6A4';
    $sky = '#CFE8F5';
    $amber = '#F4B63F';
@endphp
<svg {{ $attributes->merge(['class' => 'shrink-0']) }} viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    @if ($circle)
        <circle cx="40" cy="40" r="40" fill="#E3F7F4" />
    @endif
    <g stroke="{{ $navy }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        @switch($name)
            @case('readers')
                <circle cx="23" cy="31" r="6" fill="{{ $sky }}" />
                <path d="M12 54c0-10 5-14 11-14s11 4 11 14z" fill="{{ $sky }}" />
                <circle cx="57" cy="31" r="6" fill="{{ $sky }}" />
                <path d="M46 54c0-10 5-14 11-14s11 4 11 14z" fill="{{ $sky }}" />
                <circle cx="40" cy="28" r="7.5" fill="#fff" />
                <path d="M26 60c0-13 6-18 14-18s14 5 14 18z" fill="{{ $teal }}" />
                <circle cx="64" cy="17" r="3" fill="{{ $amber }}" stroke="none" />
                @break

            @case('multimedia')
                <rect x="13" y="17" width="54" height="36" rx="4" fill="#fff" />
                <rect x="18" y="22" width="44" height="26" rx="2" fill="{{ $sky }}" />
                <path d="M18 45l11-10 8 7 6-5 19 11" fill="none" />
                <circle cx="53" cy="29" r="3" fill="{{ $amber }}" />
                <circle cx="35" cy="33" r="7" fill="{{ $teal }}" />
                <path d="M33 30l5 3-5 3z" fill="#fff" stroke-width="1.5" />
                <path d="M34 53l-3 9h18l-3-9" fill="#fff" />
                <path d="M27 62h26" />
                @break

            @case('financial')
                <path d="M40 13l21 7v15c0 13-9 22-21 28-12-6-21-15-21-28V20z" fill="#fff" />
                <path d="M40 19l15 5v11c0 9-6 16-15 20-9-4-15-11-15-20V24z" fill="{{ $sky }}" stroke="none" />
                <path d="M31 38l6 6 12-13" stroke="{{ $teal }}" stroke-width="4" />
                <circle cx="62" cy="58" r="7" fill="{{ $amber }}" />
                <path d="M62 54.5v7" stroke-width="1.6" />
                @break

            @case('analytics')
                <rect x="13" y="15" width="54" height="40" rx="4" fill="#fff" />
                <rect x="21" y="40" width="7" height="9" rx="1" fill="{{ $teal }}" />
                <rect x="32" y="34" width="7" height="15" rx="1" fill="{{ $sky }}" />
                <rect x="43" y="29" width="7" height="20" rx="1" fill="{{ $teal }}" />
                <rect x="54" y="23" width="7" height="26" rx="1" fill="{{ $sky }}" />
                <path d="M20 31l11-7 11 4 16-9" stroke="{{ $amber }}" stroke-width="2.5" fill="none" />
                <path d="M31 55l-4 9M49 55l4 9" />
                @break

            @case('pr')
                <path d="M19 33h9v12h-9a3 3 0 0 1-3-3v-6a3 3 0 0 1 3-3z" fill="{{ $sky }}" />
                <path d="M28 33l22-12v36L28 45z" fill="{{ $teal }}" />
                <path d="M23 45l3 12h6l-2-12" fill="#fff" />
                <path d="M56 32c3 4 3 10 0 14" fill="none" stroke="{{ $teal }}" stroke-width="2.5" />
                <path d="M61 27c6 7 6 17 0 24" fill="none" stroke="{{ $amber }}" stroke-width="2.5" />
                @break

            @case('ir')
                <path d="M14 62h52" />
                <rect x="19" y="47" width="8" height="15" rx="1" fill="{{ $sky }}" />
                <rect x="31" y="39" width="8" height="23" rx="1" fill="{{ $teal }}" />
                <rect x="43" y="31" width="8" height="31" rx="1" fill="{{ $sky }}" />
                <rect x="55" y="22" width="8" height="40" rx="1" fill="{{ $teal }}" />
                <path d="M17 38l11-9 9 4 19-18" stroke="{{ $amber }}" stroke-width="2.5" fill="none" />
                <path d="M49 15h7v7" stroke="{{ $amber }}" stroke-width="2.5" fill="none" />
                @break

            @case('agencies')
                <rect x="12" y="13" width="26" height="16" rx="4" fill="#fff" />
                <path d="M19 29l-2 6 7-6" fill="#fff" />
                <circle cx="19" cy="21" r="1.6" fill="{{ $teal }}" stroke="none" />
                <circle cx="25" cy="21" r="1.6" fill="{{ $teal }}" stroke="none" />
                <circle cx="31" cy="21" r="1.6" fill="{{ $teal }}" stroke="none" />
                <circle cx="32" cy="41" r="6.5" fill="{{ $sky }}" />
                <path d="M20 64c0-11 5-15 12-15s12 4 12 15z" fill="{{ $sky }}" />
                <circle cx="52" cy="37" r="7" fill="#fff" />
                <path d="M39 64c0-12 6-17 13-17s13 5 13 17z" fill="{{ $teal }}" />
                <circle cx="62" cy="20" r="3" fill="{{ $amber }}" stroke="none" />
                @break

            @case('companies')
                <path d="M12 63h56" />
                <rect x="14" y="36" width="11" height="27" fill="{{ $sky }}" />
                <rect x="55" y="31" width="11" height="32" fill="{{ $sky }}" />
                <rect x="25" y="19" width="30" height="44" fill="#fff" />
                <path d="M40 19v-9" />
                <path d="M40 10h9l-2.5 3 2.5 3h-9" fill="{{ $amber }}" />
                @foreach ([[30, 25], [37, 25], [44, 25], [30, 33], [37, 33], [44, 33], [30, 41], [37, 41], [44, 41]] as [$x, $y])
                    <rect x="{{ $x }}" y="{{ $y }}" width="5" height="5" fill="{{ $teal }}" stroke="none" />
                @endforeach
                <rect x="35" y="51" width="10" height="12" fill="{{ $teal }}" />
                @break

            @case('industry')
                <circle cx="37" cy="43" r="22" fill="#fff" />
                <circle cx="37" cy="43" r="15" fill="{{ $sky }}" />
                <circle cx="37" cy="43" r="8" fill="{{ $teal }}" />
                <circle cx="37" cy="43" r="2.5" fill="#fff" />
                <path d="M37 43L62 18" stroke-width="2.5" />
                <path d="M57 15l7-2-2 7-5 3-3-3z" fill="{{ $amber }}" />
                @break
        @endswitch
    </g>
</svg>
