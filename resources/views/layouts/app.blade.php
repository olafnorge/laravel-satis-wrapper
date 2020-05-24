<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    {{-- Required meta tags --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{ Html::style(mix('css/app.css')) }}
    @stack('css')
</head>
<body>
    @if (Auth::user())
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-lg-5">
            <a class="navbar-brand" href="{{ route('satis.configuration.index') }}">{{ config('app.name', 'Laravel') }}</a>

            <div class="navbar-menu ml-auto">
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ Auth::user()->name }}">
                        <span class="sr-only"></span>
                        {{ Auth::user()->name }}
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item text-danger do-post" href="{{ route('auth.logout') }}" data-formid="logout-form">Logout</a>
                        {{ Form::open(['route' => 'auth.logout', 'id' => 'logout-form', 'hidden' => true]) }}{{ Form::close() }}
                    </div>
                </div>
            </div>
        </nav>
    @endif

    <div class="container-fluid">
        @includeWhen(Auth::user(), 'elements.flash')
        @yield('content')
    </div>

    {{ Html::script(mix('js/app.js')) }}
    @stack('js')
</body>
</html>
