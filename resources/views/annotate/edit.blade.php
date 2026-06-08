@extends('layouts.app')

@section('title', 'Annoteren')

@section('content')
<div class="container">
    <h1>Foto verrijken</h1>
    <p class="lead">{{ $observation->project?->name ?? 'Ljippelân' }} · geüpload @include('components.formatted-datetime', ['value' => $observation->created_at, 'date' => 'j F Y', 'showTime' => false])</p>

    @if($observation->contributor_note)
        <p><strong>Toelichting uploader:</strong> {{ $observation->contributor_note }}</p>
    @endif

    @if($photoUrl)
        <img src="{{ $photoUrl }}" alt="Geüploade foto" style="max-width:100%;border-radius:0.75rem;margin-bottom:1.5rem">
    @else
        <div class="alert alert-error">
            Foto niet gevonden op de server.
            @if($observation->storedPhotoPath())
                Pad in database: <code>{{ $observation->storedPhotoPath() }}</code>
            @else
                Er staat geen fotopad in de database bij dit moment.
            @endif
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('annotate.store', $observation) }}" method="post" class="card card-body">
        @csrf

        <label for="species">Soort *</label>
        <input type="text" name="species" id="species" value="{{ old('species', $observation->annotation?->species) }}" required placeholder="Grutto">

        <label for="count_label">Aantal *</label>
        <input type="text" name="count_label" id="count_label" value="{{ old('count_label', $observation->annotation?->count_label) }}" required placeholder="2">

        <label for="behavior">Gedrag *</label>
        <input type="text" name="behavior" id="behavior" value="{{ old('behavior', $observation->annotation?->behavior) }}" required placeholder="Baltsend op wei">

        <label for="season">Seizoen *</label>
        <input type="text" name="season" id="season" value="{{ old('season', $observation->annotation?->season) }}" required placeholder="Lente">

        <label for="story_line">Verhaalregel (publiek) *</label>
        <input type="text" name="story_line" id="story_line" value="{{ old('story_line', $observation->annotation?->story_line) }}" required maxlength="200" placeholder="Twee grutto's dansen op het natte land.">

        <label for="caption">Langere toelichting</label>
        <textarea name="caption" id="caption">{{ old('caption', $observation->annotation?->caption) }}</textarea>

        <div class="checkbox-row">
            <input type="checkbox" name="is_publishable" id="is_publishable" value="1" @checked(old('is_publishable', true))>
            <label for="is_publishable" style="margin:0">Direct publiceren op de site</label>
        </div>

        <button type="submit" class="btn">Opslaan</button>
        <a class="btn btn-secondary" href="{{ route('annotate.index') }}" style="margin-left:0.5rem">Terug</a>
    </form>

    <p style="margin-top:1rem">
        @include('components.observation-delete-form', [
            'observation' => $observation,
            'label' => 'Foto definitief verwijderen',
            'class' => 'btn btn-secondary',
            'style' => 'color:#7f1d1d;border-color:#ffb3b3',
        ])
    </p>
</div>
@endsection
