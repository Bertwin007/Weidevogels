@props([
    'partnerSlug',
    'season',
    'email' => null,
    'compact' => false,
])

<form
    action="{{ route('admin.esg-reports.send', $partnerSlug) }}"
    method="post"
    style="display:inline-flex;flex-wrap:wrap;gap:0.5rem;align-items:center;{{ $compact ? '' : 'margin:0' }}"
    onsubmit="return confirm('Rapport per e-mail versturen{{ $email ? ' naar '.$email : '' }}?')"
>
    @csrf
    <input type="hidden" name="season" value="{{ $season }}">
    @if(! filled($email))
        <input
            type="email"
            name="email"
            required
            placeholder="partner@bedrijf.nl"
            style="margin:0;min-width:12rem;font-size:{{ $compact ? '0.85rem' : '1rem' }};padding:0.35rem 0.5rem"
        >
    @endif
    <button
        type="submit"
        class="{{ $compact ? '' : 'btn btn-secondary' }}"
        style="{{ $compact ? 'background:none;border:none;color:#1b4332;cursor:pointer;text-decoration:underline;font:inherit;padding:0' : '' }}"
    >
        Verstuur naar partner
    </button>
</form>
