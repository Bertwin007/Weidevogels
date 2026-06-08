@extends('layouts.app')

@section('title', 'Greidemomenten')

@section('content')
<div class="container">
    <h1>Greidemomenten</h1>
    <p class="lead">Echte foto's en verhalen uit het greideland — steun ANF om dit landschap te behouden.</p>

    @if($errors->has('delete'))
        <div class="alert alert-error">{{ $errors->first('delete') }}</div>
    @endif

    @if($moments->isEmpty())
        <p>Nog geen momenten. <a href="{{ route('upload.create') }}">Deel de eerste foto</a>.</p>
    @else
        <div class="grid-moments">
            @foreach($moments as $moment)
                @if($moment->slug)
                <article class="card moment-card">
                    <a href="{{ route('moments.show', $moment) }}" style="text-decoration:none;color:inherit">
                        @include('components.moment-photo', ['moment' => $moment])
                        <div class="card-body">
                            <h3>{{ $moment->annotation?->story_line }}</h3>
                            <p>{{ $moment->annotation?->species }} · {{ $moment->project?->name ?? 'Ljippelân' }}</p>
                            <p class="meta">@include('components.formatted-datetime', ['value' => $moment->published_at, 'date' => 'j F Y', 'showTime' => false])</p>
                        </div>
                    </a>
                    @can('delete', $moment)
                        <div class="card-body" style="padding-top:0;display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center">
                            <a href="{{ route('moments.show', $moment) }}">Bekijk →</a>
                            @include('components.observation-delete-form', [
                                'observation' => $moment,
                                'action' => route('moments.destroy', $moment),
                                'label' => 'Verwijderen',
                            ])
                        </div>
                    @endcan
                </article>
                @endif
            @endforeach
        </div>
        {{-- pagination links weggelaten: geen extra views nodig op Plesk --}}
    @endif
</div>
@endsection
