{{-- Date, time and place as a list, then the map button. --}}
<dl class="ub-details">
    <div><dt>Hari, tanggal</dt><dd>{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</dd></div>
    <div><dt>Waktu</dt><dd>{{ $timeText }}</dd></div>
    <div><dt>Tempat</dt><dd>{{ $invitation->venue_name }}<small>{{ $invitation->venue_address }}</small></dd></div>
</dl>
@include($parts.'maps')
