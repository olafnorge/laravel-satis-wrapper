@extends('layouts.app')

@section('content')
    <div class="min-vh-100 d-flex">
        <div class="my-auto mx-auto">
            @include('elements.flash')

            <a class="btn btn-lg bg-light btn-outline-dark" role="button" href="{{ route('auth.redirect', ['provider' => 'google']) }}">
                <i class="fab fa-google"></i> Sign in with Google
            </a>
        </div>
    </div>
@endsection
