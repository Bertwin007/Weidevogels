@extends('layouts.app')

@section('title', 'Inloggen')

@section('content')
<div class="container container-narrow">
    <h1>Inloggen</h1>
    <p class="lead">Voor vrijwilligers (annotatie) en ANF-beheer.</p>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('login.store') }}" method="post" class="card card-body">
        @csrf
        <label for="email">E-mail</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Wachtwoord</label>
        <input type="password" name="password" id="password" required>

        <label class="checkbox-row">
            <input type="checkbox" name="remember" value="1">
            <span>Onthoud mij</span>
        </label>

        <button type="submit" class="btn">Inloggen</button>
    </form>
</div>
@endsection
