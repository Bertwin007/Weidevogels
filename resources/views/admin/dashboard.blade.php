@extends('layouts.app')

@section('title', 'Admin')

@section('content')
<div class="container">
    <h1>Beheer</h1>

    <div class="stats">
        <div class="stat"><strong>{{ $stats['pending'] }}</strong> wacht op annotatie</div>
        <div class="stat"><strong>{{ $stats['published'] }}</strong> gepubliceerd</div>
        <div class="stat"><strong>{{ $stats['donations'] }}</strong> donatieklicks</div>
    </div>

    <h2>Alle momenten</h2>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Verhaal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($observations as $observation)
                    <tr>
                        <td>{{ $observation->id }}</td>
                        <td><span class="badge">{{ $observation->statusValue() }}</span></td>
                        <td>{{ $observation->annotation?->story_line ?? '—' }}</td>
                        <td>
                            @if($observation->isPublished())
                                <a href="{{ route('moments.show', $observation) }}">Bekijk</a>
                                ·
                                <form action="{{ route('admin.unpublish', $observation) }}" method="post" style="display:inline">
                                    @csrf
                                    <button type="submit" style="background:none;border:none;color:#1b4332;cursor:pointer;text-decoration:underline">Offline</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
