@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="text-truncate">Overview</span>
            <a href="{{ route('satis.configuration.create') }}" class="btn btn-info float-right">
                <i class="far fa-plus-square mr-lg-2"></i>
                <span class="d-none d-lg-inline-block">Add Configuration</span>
            </a>
        </div>
        <div class="card-body">
            @if($configurations->total())
                <ul class="list-group">
                    @foreach($configurations as $configuration)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-truncate">{{ $configuration->name }}</span>
                            <div class="btn-group float-right" role="group" aria-label="Actions">
                                <a href="{{ route('satis.configuration.details', ['uuid' => $configuration->uuid]) }}" class="btn btn-info">
                                    <i class="fas fa-info mr-lg-2"></i><span class="d-none d-lg-inline-block">Details</span>
                                </a>
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
                        </li>
                    @endforeach
                </ul>
            @else
                <p>
                    No configurations defined.
                    <a href="{{ route('satis.configuration.create') }}">Create one now.</a>
                </p>
            @endif
        </div>
        {{ $configurations->links() }}
    </div>
@endsection
