@props(['moment', 'style' => ''])

@if($moment->photoExistsOnDisk())
    <img src="{{ $moment->photo_url }}" alt="{{ $moment->annotation?->story_line ?? 'Greidemoment' }}" @if($style) style="{{ $style }}" @endif>
@else
    <div @if($style) style="{{ $style }}" @else style="aspect-ratio:4/3;background:#d8e2dc;display:flex;align-items:center;justify-content:center;color:#52796f;font-size:0.9rem;padding:1rem;text-align:center" @endif>
        Foto niet beschikbaar
    </div>
@endif
