@extends('layouts.app')

@section('content')
    <section class="card">
        {{ Form::open([
                'method' => 'post',
                'route' => request()->route('index')
                    ? [request()->route()->getName(), request()->route('uuid'), request()->route('index')]
                    : [request()->route()->getName(), request()->route('uuid')]
        ]) }}
            <div class="card-body">
                <h5 class="card-title">Satis Repository</h5>
                <p>The URL will only be validated if it's formal valid. There will be no check if the repository is available.</p>

                @if($errors->has('url'))
                    <div class="bs-callout bs-callout-danger mb-3">
                        <h5 class="card-title">{{{ session()->get('error') }}}</h5>
                        <ul>
                            @foreach($errors->get('url') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                {{ Form::text('url', isset($url) ? $url : null, ['class' => 'form-control', 'placeholder' => 'http://example.com']) }}
            </div>


            <div class="card-footer">
                <a href="{{ route('satis.configuration.details', ['uuid' => request()->route('uuid')]) }}" class="btn btn-info">Back</a>
                {{ Form::submit('Save', ['class' => 'btn btn-primary float-right']) }}
            </div>
        {{ Form::close() }}
    </section>
@endsection
