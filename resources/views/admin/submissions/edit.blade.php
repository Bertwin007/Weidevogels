@extends('layouts.app')

@section('title', 'Inzending bewerken')

@section('content')
<div class="container">
    <p class="meta"><a href="{{ route('admin.submissions.index') }}">← Alle inzendingen</a></p>
    <h1>Inzending #{{ $observation->id }}</h1>
    <p class="lead">
        Ingezonden @include('components.formatted-datetime', ['value' => $observation->created_at, 'showTime' => true])
        · status: {{ \App\Support\ObservationLabels::status($observation->statusValue()) }}
    </p>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if($photoUrl)
        <img src="{{ $photoUrl }}" alt="Geüploade foto" style="max-width:100%;max-height:18rem;border-radius:0.75rem;margin-bottom:1.5rem">
    @else
        <div class="alert alert-info">Geen foto beschikbaar op de server.</div>
    @endif

    <form method="post" action="{{ route('admin.submissions.update', $observation) }}" class="card card-body">
        @csrf
        @method('PUT')

        <h2>Inzending</h2>

        <label for="project_id">Project *</label>
        <select name="project_id" id="project_id" required>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" @selected(old('project_id', $observation->project_id) == $project->id)>{{ $project->name }}</option>
            @endforeach
        </select>

        <label for="guest_name">Naam inzender</label>
        <input type="text" name="guest_name" id="guest_name" value="{{ old('guest_name', $observation->guest_name) }}">

        <label for="guest_email">E-mail inzender</label>
        <input type="email" name="guest_email" id="guest_email" value="{{ old('guest_email', $observation->guest_email) }}">

        <label for="contributor_note">Toelichting inzender</label>
        <textarea name="contributor_note" id="contributor_note">{{ old('contributor_note', $observation->contributor_note) }}</textarea>

        <label for="status">Status *</label>
        <select name="status" id="status" required>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $currentStatus) === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <h2 style="margin-top:1.5rem">Annotatie</h2>

        @include('components.ai-suggestion-panel', [
            'observation' => $observation,
            'aiEnabled' => (bool) config('greidefugels.ai.enabled'),
            'aiConfigured' => app(\App\Services\AiPreScanService::class)->isConfigured(),
        ])

        <label for="species">Soort</label>
        <input type="text" name="species" id="species" value="{{ old('species', $observation->annotation?->species) }}">

        <label for="count_label">Aantal</label>
        <input type="text" name="count_label" id="count_label" value="{{ old('count_label', $observation->annotation?->count_label) }}">

        <label for="behavior">Gedrag</label>
        <input type="text" name="behavior" id="behavior" value="{{ old('behavior', $observation->annotation?->behavior) }}">

        <label for="season">Seizoen</label>
        <input type="text" name="season" id="season" value="{{ old('season', $observation->annotation?->season) }}">

        <label for="story_line">Verhaalregel (publiek)</label>
        <input type="text" name="story_line" id="story_line" value="{{ old('story_line', $observation->annotation?->story_line) }}" maxlength="200">

        <label for="caption">Langere toelichting</label>
        <textarea name="caption" id="caption">{{ old('caption', $observation->annotation?->caption) }}</textarea>

        @if($observation->annotation?->annotator)
            <p class="meta">Laatst geannoteerd door {{ $observation->annotation->annotator->name }}
                @if($observation->annotation->updated_at)
                    op @include('components.formatted-datetime', ['value' => $observation->annotation->updated_at, 'showTime' => true])
                @endif
            </p>
        @endif

        @if($observation->published_at)
            <p class="meta">Gepubliceerd op @include('components.formatted-datetime', ['value' => $observation->published_at, 'showTime' => true])</p>
        @endif

        <div style="margin-top:1.5rem;display:flex;flex-wrap:wrap;gap:0.75rem;align-items:center">
            <button type="submit" class="btn">Opslaan</button>
            @if($observation->isPublished() && $observation->slug)
                <a class="btn btn-secondary" href="{{ route('moments.show', $observation) }}">Bekijk op site</a>
            @endif
            @if($observation->isPendingAnnotation())
                <a class="btn btn-secondary" href="{{ route('annotate.edit', $observation) }}">Naar annotatiescherm</a>
            @endif
        </div>
    </form>

    <p style="margin-top:1rem">
        @include('components.observation-delete-form', [
            'observation' => $observation,
            'label' => 'Inzending definitief verwijderen',
            'class' => 'btn btn-secondary',
            'style' => 'color:#7f1d1d;border-color:#ffb3b3',
        ])
    </p>
</div>
@endsection
