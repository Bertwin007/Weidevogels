@extends('layouts.app')

@section('title', $observation->annotation?->story_line ?? 'Moment')

@section('content')
<div class="container">
    <article>
        <p class="meta">{{ $observation->project?->name ?? 'Ljippelân' }} · {{ $observation->published_at?->format('d F Y') }}</p>
        <h1>{{ $observation->annotation?->story_line }}</h1>

        <img src="{{ $observation->photo_url }}" alt="" style="width:100%;max-height:32rem;object-fit:cover;border-radius:0.75rem;margin:1rem 0 1.5rem">

        <p><strong>{{ $observation->annotation?->species }}</strong> · {{ $observation->annotation?->count_label }} · {{ $observation->annotation?->behavior }} · {{ $observation->annotation?->season }}</p>

        @if($observation->annotation?->caption)
            <p>{{ $observation->annotation->caption }}</p>
        @endif

        <p style="margin-top:2rem">
            <a class="btn btn-donate" href="{{ route('donate.moment', $observation) }}">Steun dit greideland</a>
            <a class="btn btn-secondary" href="{{ route('upload.create') }}" style="margin-left:0.5rem">Deel ook een foto</a>
        </p>
    </article>
</div>
@endsection
