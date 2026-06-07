@extends('layouts.app')

@section('title', 'Annoteren')

@section('content')
<div class="container">
    <h1>Wachtrij annotatie</h1>
    <p class="lead">{{ $queue->total() }} foto(s) wachten op verrijking.</p>

    @if($queue->isEmpty())
        <p>Geen openstaande foto's. 🎉</p>
    @else
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Project</th>
                        <th>Geüpload</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($queue as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->project?->name ?? '—' }}</td>
                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                            <td><a href="{{ route('annotate.edit', $item) }}">Annoteren →</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- pagination links weggelaten --}}
    @endif
</div>
@endsection
