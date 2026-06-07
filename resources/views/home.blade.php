@extends('layouts.app')

@section('title', 'Home')

@section('content')
<section class="hero">
    <div class="hero-inner">
        <p class="lead" style="margin-bottom:0.75rem">Agrarisch Natuurfonds Fryslân · Ljippelân</p>
        <h1>Vastleggen wat leeft in ons greideland</h1>
        <p class="lead">
            Deel een greidemoment, lees verhalen over weidevogels en steun het werk van ANF in Fryslân.
        </p>
        <p style="margin-top:1.5rem">
            <a class="btn" href="{{ route('upload.create') }}">Deel jouw foto</a>
            <a class="btn btn-donate" href="{{ route('donate') }}" style="margin-left:0.5rem">Steun dit greideland</a>
        </p>
    </div>
</section>

<div class="container">
    @if($stats['moments'] > 0)
        <div class="stats">
            <div class="stat"><strong>{{ $stats['moments'] }}</strong> greidemomenten</div>
            <div class="stat"><strong>{{ $stats['donations'] }}</strong> keer gesteund</div>
        </div>
    @endif

    <h2>Laatste momenten</h2>

    @if($moments->isEmpty())
        <p class="lead">Nog geen momenten gepubliceerd. <a href="{{ route('upload.create') }}">Wees de eerste</a> die een foto deelt.</p>
    @else
        <div class="grid-moments">
            @foreach($moments as $moment)
                <a href="{{ route('moments.show', $moment) }}" class="card moment-card" style="text-decoration:none;color:inherit">
                    <img src="{{ $moment->photo_url }}" alt="">
                    <div class="card-body">
                        <h3>{{ $moment->annotation?->story_line }}</h3>
                        <p>{{ $moment->annotation?->species }} · {{ $moment->project->name }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        <p style="margin-top:1.5rem"><a href="{{ route('moments.index') }}">Alle momenten →</a></p>
    @endif
</div>
@endsection
