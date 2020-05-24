@extends('layouts.app')

@section('content')
    <div class="h-100 d-flex" style="min-height: 100vh">
        <div class="container my-auto">
            <section class="card mx-auto">
                <div class="card-body">
                    @include('elements.flash')

                    <div class="row">
                        <div class="col-12 col-lg-4 mb-3 mb-lg-0">
                            <a class="input-group btn btn-outline-secondary btn-block bg-light" href="{{ route('auth.redirect', ['provider' => 'github']) }}">
                                <div class="input-group-prepend">
                                    <img src="{{ asset('img/GitHub-Mark-64px.png') }}" height="66">
                                </div>
                                <span class="form-control form-control-plaintext border-0 pt-4 text-secondary">Sign In with Github</span>
                            </a>
                        </div>
                        <div class="col-12 col-lg-4">
                            <a class="input-group btn btn-outline-secondary btn-block bg-light" href="{{ route('auth.redirect', ['provider' => 'google']) }}">
                                <div class="input-group-prepend">
                                    <img src="{{ asset('img/btn_google_dark_normal_ios.svg') }}" height="66">
                                </div>
                                <span class="form-control form-control-plaintext border-0 pt-4 text-secondary">Sign In with Google</span>
                            </a>
                        </div>
                        <div class="col-12 col-lg-4 mt-3 mt-lg-0">
                            <a class="input-group btn btn-outline-secondary btn-block bg-light" href="{{ route('auth.redirect', ['provider' => 'linkedin']) }}">
                                <div class="input-group-prepend">
                                    <img src="{{ asset('img/In-2CRev-66px-R.png') }}" height="66">
                                </div>
                                <span class="form-control form-control-plaintext border-0 pt-4 text-secondary">Sign In with LinkedIn</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
