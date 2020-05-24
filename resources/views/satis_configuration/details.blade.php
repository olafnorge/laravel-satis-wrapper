@extends('layouts.app')

@push('css')
    {{ Html::style(mix('css/jsoneditor.css')) }}
@endpush

@push('js')
    {{ Html::script(mix('js/jsoneditor.js')) }}
    {{ Html::script(mix('js/prettycron.js')) }}

    <script>
        let jsoneditor = {
            "config": {!! $configuration->configuration !!},
            "crontab": '{{ $configuration->crontab }}',
            "meta": {!! $metaSchema !!},
            "schema": {!! $schema !!}
        };
    </script>

    {{ Html::script(mix('js/satis_details.js')) }}
@endpush

@section('content')
    <section class="card">
        <div class="card-body">
            <h5 class="card-title">Configuration »{{ $configuration->name }}« Details</h5>
            @if($avgBuildTime)
                <h6 class="card-subtitle mb-2 text-muted">Avg build time: {{ $avgBuildTime }}</h6>
            @endif

            <h5 class="card-title clearfix align-middle">
                Settings
                <ul class="list-unstyled list-inline float-right">
                    @if($configuration->repositories)
                        <li class="list-inline-item"><a href="{{ $configuration->homepage }}" target="_blank" class="btn btn-info">Homepage</a></li>
                    @endif
                    <li class="list-inline-item"><a href="{{ route('satis.configuration.edit', ['uuid' => $configuration->uuid]) }}" class="btn btn-info">Edit</a></li>
                    @if($configuration->repositories)
                        <li class="list-inline-item">
                            {{ Form::open(['url' => route('satis.configuration.build', ['uuid' => $configuration->uuid]), 'class' => 'd-inline']) }}
                                <button type="submit" class="btn btn-info">Build</button>
                            {{ Form::close() }}
                        </li>
                        <li class="list-inline-item">
                            {{ Form::open(['url' => route('satis.configuration.purge', ['uuid' => $configuration->uuid]), 'class' => 'd-inline']) }}
                                <button type="submit" class="btn btn-danger" data-confirm="Are you sure you want to purge »{{ $configuration->name }}«?">Purge</button>
                            {{ Form::close() }}
                        </li>
                    @endif
                </ul>
            </h5>

            <div id="configuration-jsoneditor"></div>

            <h5 class="card-title mt-4">Satis repository</h5>
            <pre class="bg-light"><code>{
    "repositories": [{
        "type": "composer",
        "url": "{{ rtrim($configuration->homepage, '/') }}"
    }]
}</code></pre>
            <p class="text-right small"><em>Add this Satis repository to your <kbd>composer.json</kbd></em></p>

            <h5 class="card-title">Secured with password</h5>
            @if($configuration->password_secured)
                <div class="row">
                    <div class="col-12 col-lg-2"><span class="form-control form-control-plaintext border-0 pt-2 bg-light">composer</span></div>
                    <div class="col-12 col-lg-10"><span class="form-control form-control-plaintext border-0 pt-2 bg-light">{{ $configuration->password }}</span></div>
                </div>
            @else
                <span class="form-control form-control-plaintext border-0 pt-2 bg-light">no password set</span>
            @endif

            <h5 class="card-title mt-4">Crontab</h5>
            @if($configuration->crontab)
                <span class="form-control form-control-plaintext border-0 pt-2 bg-light">{{ $configuration->crontab }}</span>
                <p class="text-right small"><em><span id="crontab_human_readable"></span> - <span id="crontab_next"></span></em></p>
            @else
                <span class="form-control form-control-plaintext border-0 pt-2 bg-light">not scheduled</span>
            @endif

            <h5 class="card-title clearfix mt-4">
                Repositories
                <span class="float-right"><a href="{{ route('satis.repository.create', ['uuid' => $configuration->uuid]) }}" class="btn btn-info">Add new Repository</a></span>
            </h5>

            @if($repositories->total())
                <ul class="list-group">
                    @foreach($repositories as $index => $repository)
                        @if(array_has($repository, 'url') || array_has($repository, 'package.name'))
                            <li class="list-group-item clearfix">
                                {{ array_get($repository, 'url') ?: array_get($repository, 'package.name') }}
                                <span class="float-right">
                                    <a href="{{ route('satis.repository.edit', ['uuid' => $configuration->uuid, 'index' => $index + 1]) }}" class="btn btn-info">Edit</a>
                                    {{ Form::open(['url' => route('satis.repository.delete', ['uuid' => $configuration->uuid, 'index' => $index + 1]), 'class' => 'd-inline', 'method' => 'DELETE']) }}
                                        <button type="submit" class="btn btn-danger" data-confirm="Are you sure you want to delete »{{ array_get($repository, 'url') ?: array_get($repository, 'package.name') }}«?">Delete</button>
                                    {{ Form::close() }}
                                </span>
                            </li>
                        @endif
                    @endforeach
                </ul>

                {{ $repositories->links() }}
            @else
                No repositories defined.
            @endif


        </div>

        <div class="card-footer">
            <a href="{{ route('satis.configuration.index') }}" class="btn btn-info">Back</a>
        </div>
    </section>
@endsection
