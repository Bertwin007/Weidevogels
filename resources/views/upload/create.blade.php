@extends('layouts.app')

@section('title', 'Deel een foto')

@section('content')
<div class="container container-narrow">
    <h1>Deel een greidemoment</h1>
    <p class="lead">Upload een foto van het greideland bij <strong>{{ $project->name }}</strong>. Geen account nodig — een vrijwilliger verrijkt je foto met een verhaal.</p>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('upload.store') }}" method="post" enctype="multipart/form-data" class="card card-body">
        @csrf

        <label for="photo">Foto *</label>
        <input type="file" name="photo" id="photo" accept="image/*" required>
        <p class="field-help">JPG of PNG, max. 10 MB.</p>

        <label for="guest_name">Je naam (optioneel)</label>
        <input type="text" name="guest_name" id="guest_name" value="{{ old('guest_name') }}" maxlength="120">

        <label for="guest_email">E-mail (optioneel)</label>
        <input type="email" name="guest_email" id="guest_email" value="{{ old('guest_email') }}">

        <label for="contributor_note">Korte toelichting (optioneel)</label>
        <textarea name="contributor_note" id="contributor_note" maxlength="1000">{{ old('contributor_note') }}</textarea>

        <button type="submit" class="btn">Foto versturen</button>
    </form>
</div>
@endsection
