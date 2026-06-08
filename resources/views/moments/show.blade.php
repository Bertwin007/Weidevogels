@extends('layouts.app')

@section('title', $observation->annotation?->story_line ?? 'Moment')

@section('content')
<div class="container">
    <article>
        <p class="meta">{{ $observation->project?->name ?? 'Ljippelân' }} · @include('components.formatted-datetime', ['value' => $observation->published_at, 'date' => 'j F Y', 'showTime' => false])</p>
        <h1>{{ $observation->annotation?->story_line }}</h1>

        @include('components.moment-photo', [
            'moment' => $observation,
            'style' => 'width:100%;max-height:32rem;object-fit:cover;border-radius:0.75rem;margin:1rem 0 1.5rem',
        ])

        <p><strong>{{ $observation->annotation?->species }}</strong> · {{ $observation->annotation?->count_label }} · {{ $observation->annotation?->behavior }} · {{ $observation->annotation?->season }}</p>

        @if($observation->annotation?->caption)
            <p>{{ $observation->annotation->caption }}</p>
        @endif

        <div style="margin-top:2rem;display:flex;flex-wrap:wrap;gap:0.75rem;align-items:center">
            <a class="btn btn-donate" href="{{ route('donate.moment', $observation) }}">Steun dit greideland</a>
            <a class="btn btn-secondary" href="{{ route('home') }}#scan">Doe ook een Greide-scan</a>
            @include('components.observation-delete-form', [
                'observation' => $observation,
                'action' => route('moments.destroy', $observation),
                'label' => 'Verwijder moment',
                'class' => 'btn btn-secondary',
                'style' => 'color:#7f1d1d;border-color:#ffb3b3',
            ])
        </div>
    </article>
</div>
@endsection
