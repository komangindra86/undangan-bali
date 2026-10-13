<a href="https://play.google.com/store/apps/details?id=com.balisantih.undanganbali" target="_blank" rel="noopener" style="color: inherit; text-decoration: underline; text-underline-offset: 3px;">dibuat dengan bali santih</a>
@if ($invitation->exists && $invitation->status === 'published' && ! ($isPreview ?? false) && ! $invitation->isRetentionExempt())
&middot; <a href="{{ route('invitations.report', $invitation->slug) }}" rel="nofollow" style="color: inherit; text-decoration: underline; text-underline-offset: 3px;">laporkan undangan ini</a>
@endif
@include('invitations.partials.music-credit')
