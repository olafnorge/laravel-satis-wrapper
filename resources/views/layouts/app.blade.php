<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{ Html::style(mix('css/app.css')) }}
    @stack('css')
</head>
<body>
    @if (Auth::user())
        <nav class="navbar navbar-light bg-light mb-5">
            <div class="container">
                <a class="navbar-brand" href="{{ route('satis.configuration.index') }}">{{ config('app.name', 'Laravel') }}</a>

                <div class="navbar-menu">
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ Auth::user()->name }}">
                            <span class="sr-only"></span>
                            {{ Auth::user()->name }}
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item text-danger" href="{{ route('auth.logout') }}" id="logout-button">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    @endif

    <section class="section-content {{ Auth::user() ? 'pb-5' : '' }}" {{ Auth::guest() ? 'style="min-height: 100vh"' : '' }}>
        <div class="container" {{ Auth::guest() ? 'style="min-height: 100vh"' : '' }}>
            @includeWhen(Auth::user(), 'elements.flash')
            @yield('content')
        </div>
    </section>

    {{ Form::open(['route' => 'auth.logout', 'id' => 'logout-form', 'class' => 'd-none']) }}{{ Form::close() }}

    {{ Html::script(mix('js/manifest.js')) }}
    {{ Html::script(mix('js/vendor.js')) }}
    {{ Html::script(mix('js/app.js')) }}
    @stack('js')
</body>
</html>
