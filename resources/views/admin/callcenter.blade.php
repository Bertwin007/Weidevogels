@extends('layouts.app')

@section('title', 'Callcenter')

@push('head')
<link rel="stylesheet" href="{{ asset('css/callcenter-admin.css') }}">
@endpush

@section('content')
<div class="container">
    <h1>Callcenter-werkbank</h1>
    <p class="lead">Inzendingen uit de Greide-scan worden belredenen. Gebruik dit overzicht voor outbound-gesprekken en opvolging.</p>

    <p style="margin-bottom:1.5rem">
        <a href="{{ route('admin.dashboard') }}">← Terug naar beheer</a>
        · <a href="{{ route('admin.submissions.index') }}">Alle inzendingen</a>
    </p>

    @include('components.callcenter-workbench', ['kpis' => $kpis, 'queue' => $queue])
</div>
@endsection
