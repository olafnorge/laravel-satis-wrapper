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
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="text-truncate">
                Configuration »{{ $configuration->name }}« Details
                @if($avgBuildTime)
                    <div class="h6 text-muted">Avg build time: {{ $avgBuildTime }}</div>
                @endif
            </span>
            <div class="btn-group float-right" role="group" aria-label="Actions">
                @if($configuration->repositories && repository_has_builds($configuration->uuid))
                    <a href="{{ $configuration->homepage }}" target="_blank" class="btn btn-info">
                        <i class="fas fa-home mr-lg-2"></i><span class="d-none d-lg-inline-block">Homepage</span>
                    </a>
                @endif
                <a href="{{ route('satis.configuration.edit', ['uuid' => $configuration->uuid]) }}" class="btn btn-info">
                    <i class="fas fa-pencil-alt mr-lg-2"></i><span class="d-none d-lg-inline-block">Edit</span>
                </a>
                @if($configuration->repositories)
                    <button class="btn btn-info do-post" data-formid="{{ sprintf('build-configuration-%s', $configuration->uuid) }}">
                        <i class="fas fa-cog mr-lg-2"></i><span class="d-none d-lg-inline-block">Build</span>
                        {{ Form::open(['url' => route('satis.configuration.build', ['uuid' => $configuration->uuid]), 'hidden' => true, 'id' => sprintf('build-configuration-%s', $configuration->uuid)]) }}{{ Form::close() }}
                    </button>
                    @if(repository_has_builds($configuration->uuid))
                        <button class="btn btn-danger do-post" data-confirm="Are you sure you want to purge »{{ $configuration->name }}«?" data-formid="{{ sprintf('purge-configuration-%s', $configuration->uuid) }}">
                            <i class="fas fa-recycle mr-lg-2"></i><span class="d-none d-lg-inline-block">Purge</span>
                            {{ Form::open(['url' => route('satis.configuration.purge', ['uuid' => $configuration->uuid]), 'hidden' => true, 'id' => sprintf('purge-configuration-%s', $configuration->uuid)]) }}{{ Form::close() }}
                        </button>
                    @endif
                @endif
            </div>
        </div>
        <div class="card-body">

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
                    <div class="col-12 col-lg-10">
                        <div class="input-group bg-light">
                            <div class="input-group-prepend">
                                <button class="btn btn-clipboard" data-toggle="tooltip" title="Copy to clipboard" data-clipboard-text="{{ $configuration->password }}">
                                    <i class="far fa-clipboard"></i>
                                </button>
                            </div>
                            <span class="form-control form-control-plaintext border-0 pt-2">
                                {{ str_limit($configuration->password, 3, '•••') }}
                            </span>
                        </div>
                    </div>
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

            <h5 class="card-title d-flex justify-content-between align-items-center mt-4">
                Repositories
                <a href="{{ route('satis.repository.create', ['uuid' => $configuration->uuid]) }}" class="btn btn-info float-right">
                    <i class="far fa-plus-square mr-lg-2"></i>
                    <span class="d-none d-lg-inline-block">Add new Repository</span>
                </a>
            </h5>

            @if($repositories->total())
                <ul class="list-group mb-4">
                    @foreach($repositories as $index => $repository)
                        @if(array_has($repository, 'url') || array_has($repository, 'package.name'))
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-truncate">{{ array_get($repository, 'url') ?: array_get($repository, 'package.name') }}</span>
                                <div class="btn-group float-right" role="group" aria-label="Actions">
                                    <a href="{{ route('satis.repository.edit', ['uuid' => $configuration->uuid, 'index' => $index + 1]) }}" class="btn btn-info">
                                        <i class="fas fa-pencil-alt mr-lg-2"></i><span class="d-none d-lg-inline-block">Edit</span>
                                    </a>
                                    <button class="btn btn-danger do-post" data-formid="{{ sprintf('delete-repository-%s-%s', $configuration->uuid, $index + 1) }}" data-confirm="Are you sure you want to delete »{{ array_get($repository, 'url') ?: array_get($repository, 'package.name') }}«?">
                                        <i class="fas fa-trash-alt mr-lg-2"></i><span class="d-none d-lg-inline-block">Delete</span>
                                        {{ Form::open(['url' => route('satis.repository.delete', ['uuid' => $configuration->uuid, 'index' => $index + 1]), 'hidden' => true, 'id' => sprintf('delete-repository-%s-%s', $configuration->uuid, $index + 1), 'method' => 'DELETE']) }}{{ Form::close() }}
                                    </button>
                                </div>
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
    </div>
@endsection
