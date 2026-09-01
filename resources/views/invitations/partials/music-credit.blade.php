@if(isset($invitation) && $invitation->music_type === 'default' && $invitation->music?->attribution)
    <span data-music-credit style="display: block; margin: 16px auto 0; max-width: 480px; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.7; letter-spacing: normal; text-transform: none; overflow-wrap: anywhere;">
        {{ $invitation->music->attribution }}<br>
        <a href="{{ $invitation->music->source_url }}" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">Sumber musik</a>
        &middot;
        <a href="{{ $invitation->music->license_url }}" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">{{ $invitation->music->license_code }}</a>
    </span>
@endif
