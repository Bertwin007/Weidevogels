@extends('layouts.app')

@section('title', 'Greidemomenten')

@section('content')
<div class="container">
    <h1>Greidemomenten</h1>
    <p class="lead">Echte foto's en verhalen uit het greideland — steun ANF om dit landschap te behouden.</p>

    @if($moments->isEmpty())
        <p>Nog geen momenten. <a href="{{ route('upload.create') }}">Deel de eerste foto</a>.</p>
    @else
        <div class="grid-moments">
            @foreach($moments as $moment)
                @if($moment->slug)
                <a href="{{ route('moments.show', $moment) }}" class="card moment-card" style="text-decoration:none;color:inherit">
                    <img src="{{ $moment->photo_url }}" alt="">
                    <div class="card-body">
                        <h3>{{ $moment->annotation?->story_line }}</h3>
                        <p>{{ $moment->annotation?->species }} · {{ $moment->project?->name ?? 'Ljippelân' }}</p>
                        <p class="meta">{{ $moment->published_at?->format('d F Y') }}</p>
                    </div>
                </a>
                @endif
            @endforeach
        </div>
        {{-- pagination links weggelaten: geen extra views nodig op Plesk --}}
    @endif
</div>
@endsection
