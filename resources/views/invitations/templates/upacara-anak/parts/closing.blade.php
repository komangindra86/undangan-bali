{{-- Closing words, the family's names and the share button. --}}
<p>Atas kehadiran dan doa restu Bapak/Ibu/Saudara/i, kami mengucapkan terima kasih.</p>
<h2>Om Shanti, Shanti, Shanti Om</h2>
<p class="ub-family">Kami yang berbahagia,<br><strong>{{ $parents }}</strong>{{ $invitation->isMultiFamily() ? '' : ' beserta keluarga' }}</p>
<button type="button" class="ub-button" data-ua-share>Bagikan undangan</button>
<p class="ub-credit">@include('invitations.partials.app-credit')</p>
