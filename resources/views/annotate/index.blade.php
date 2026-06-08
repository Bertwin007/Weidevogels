@extends('layouts.app')

@section('title', 'Annoteren')

@section('content')
<div class="container">
    <h1>Wachtrij annotatie</h1>
    <p class="lead">{{ $queueTotal }} foto(s) wachten op verrijking.</p>

    @if($queue->isEmpty())
        <p>Geen openstaande foto's. 🎉</p>
    @else
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Project</th>
                        <th>AI</th>
                        <th>Geüpload</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($queue as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->project?->name ?? '—' }}</td>
                            <td>
                                @if($item->hasAiSuggestion())
                                    <span class="badge" title="AI-voorstel beschikbaar">AI</span>
                                @else
                                    <span class="meta">—</span>
                                @endif
                            </td>
                            <td>@include('components.formatted-datetime', ['value' => $item->created_at, 'showTime' => true])</td>
                            <td>
                                <a href="{{ route('annotate.edit', $item) }}">Annoteren →</a>
                                ·
                                @include('components.observation-delete-form', ['observation' => $item])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- pagination links weggelaten --}}
    @endif
</div>
@endsection
