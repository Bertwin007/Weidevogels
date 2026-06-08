@extends('layouts.app')

@section('title', 'Inzendingen')

@section('content')
<div class="container">
    <p class="meta"><a href="{{ route('admin.dashboard') }}">← Beheer</a></p>
    <h1>Alle inzendingen</h1>
    <p class="lead">Overzicht van alle foto’s en momenten — bekijken, aanpassen en beheren.</p>

    <div class="stats">
        <div class="stat"><strong>{{ $counts['all'] }}</strong> totaal</div>
        <div class="stat"><strong>{{ $counts['pending'] }}</strong> wachtend</div>
        <div class="stat"><strong>{{ $counts['published'] }}</strong> gepubliceerd</div>
        <div class="stat"><strong>{{ $counts['rejected'] }}</strong> afgewezen</div>
        <div class="stat"><strong>{{ $counts['unpublished'] }}</strong> offline</div>
    </div>

    <form method="get" class="card card-body" style="margin-bottom:1.25rem">
        <label for="q">Zoeken</label>
        <div style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:end">
            <input type="text" name="q" id="q" value="{{ $search }}" placeholder="Naam, e-mail, toelichting of nr." style="flex:1;min-width:14rem;margin:0">
            @if($filter !== 'all')
                <input type="hidden" name="status" value="{{ $filter }}">
            @endif
            <button type="submit" class="btn">Zoeken</button>
            @if($search !== '')
                <a href="{{ route('admin.submissions.index', ['status' => $filter !== 'all' ? $filter : null]) }}" class="btn btn-secondary">Wis</a>
            @endif
        </div>
    </form>

    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1.25rem">
        @foreach([
            'all' => 'Alles',
            'pending' => 'Wachtend',
            'published' => 'Gepubliceerd',
            'rejected' => 'Afgewezen',
            'unpublished' => 'Offline',
        ] as $key => $label)
            <a href="{{ route('admin.submissions.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => $search ?: null])) }}"
               class="badge" style="{{ $filter === $key ? 'background:#40916c;color:#fff' : '' }}">{{ $label }} ({{ $counts[$key] }})</a>
        @endforeach
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Nr.</th>
                    <th>Foto</th>
                    <th>Inzender</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Ingezonden</th>
                    <th>Verhaal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $submission)
                    <tr>
                        <td>{{ $submission->id }}</td>
                        <td>
                            @if($submission->photoExistsOnDisk())
                                <img src="{{ route('annotate.photo', $submission) }}" alt="" style="width:3.5rem;height:3.5rem;object-fit:cover;border-radius:0.35rem">
                            @else
                                <span class="meta">—</span>
                            @endif
                        </td>
                        <td>
                            {{ $submission->guest_name ?: 'Anoniem' }}
                            @if($submission->guest_email)
                                <br><span class="meta">{{ $submission->guest_email }}</span>
                            @endif
                        </td>
                        <td>{{ $submission->project?->name ?? '—' }}</td>
                        <td><span class="badge">{{ \App\Support\ObservationLabels::status($submission->statusValue()) }}</span></td>
                        <td>@include('components.formatted-datetime', ['value' => $submission->created_at, 'showTime' => true])</td>
                        <td>{{ $submission->annotation?->story_line ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.submissions.edit', $submission) }}">Bewerken</a>
                            @if($submission->isPublished() && $submission->slug)
                                · <a href="{{ route('moments.show', $submission) }}">Bekijk</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Geen inzendingen gevonden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($submissions->hasPages())
        <div style="margin-top:1rem;display:flex;gap:1rem;align-items:center">
            @if($submissions->onFirstPage())
                <span class="meta">← Vorige</span>
            @else
                <a href="{{ $submissions->previousPageUrl() }}">← Vorige</a>
            @endif
            <span class="meta">Pagina {{ $submissions->currentPage() }} van {{ $submissions->lastPage() }}</span>
            @if($submissions->hasMorePages())
                <a href="{{ $submissions->nextPageUrl() }}">Volgende →</a>
            @else
                <span class="meta">Volgende →</span>
            @endif
        </div>
    @endif
</div>
@endsection
