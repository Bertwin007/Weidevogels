@extends('layouts.app')

@section('title', 'Beheer')

@section('content')
<div class="container">
    <h1>Beheer</h1>
    <p class="lead">Overzicht van het platform Agrarisch Natuurfonds Fryslân.</p>

    <div class="stats">
        <div class="stat"><strong>{{ $stats['pending'] }}</strong> wacht op annotatie</div>
        <div class="stat"><strong>{{ $stats['published'] }}</strong> gepubliceerd</div>
        <div class="stat"><strong>{{ $stats['donations'] }}</strong> donatieklicks</div>
    </div>

    <p style="margin:1.5rem 0;display:flex;flex-wrap:wrap;gap:0.75rem">
        <a class="btn" href="{{ route('admin.submissions.index') }}">Alle inzendingen beheren</a>
        <a class="btn btn-secondary" href="{{ route('admin.callcenter') }}">Callcenter-werkbank</a>
    </p>

    <h2>Laatste inzendingen</h2>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Nr.</th>
                    <th>Status</th>
                    <th>Verhaal</th>
                    <th>Ingezonden</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($observations as $observation)
                    <tr>
                        <td>{{ $observation->id }}</td>
                        <td><span class="badge">{{ \App\Support\ObservationLabels::status($observation->statusValue()) }}</span></td>
                        <td>{{ $observation->annotation?->story_line ?? '—' }}</td>
                        <td>@include('components.formatted-datetime', ['value' => $observation->created_at, 'showTime' => true])</td>
                        <td>
                            <a href="{{ route('admin.submissions.edit', $observation) }}">Bewerken</a>
                            @if($observation->isPublished() && $observation->slug)
                                · <a href="{{ route('moments.show', $observation) }}">Bekijk</a>
                                ·
                                @can('unpublish', $observation)
                                    <form action="{{ route('admin.unpublish', $observation) }}" method="post" style="display:inline">
                                        @csrf
                                        <button type="submit" style="background:none;border:none;color:#1b4332;cursor:pointer;text-decoration:underline">Offline halen</button>
                                    </form>
                                    ·
                                @endcan
                            @endif
                            @include('components.observation-delete-form', ['observation' => $observation])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
