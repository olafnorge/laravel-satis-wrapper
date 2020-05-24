@extends('layouts.app')

@section('content')
    <section class="card">
        <div class="card-body">
            <h5 class="card-title clearfix">
                Satis Configurations
                <span class="float-right"><a href="{{ route('satis.configuration.create') }}" class="btn btn-info">Add Configuration</a></span>
            </h5>

            @if($configurations->total())
                <ul class="list-group">
                    @foreach($configurations as $configuration)
                        <li class="list-group-item clearfix">
                            {{ $configuration->name }}
                            <span class="float-right">
                                <a href="{{ route('satis.configuration.details', ['uuid' => $configuration->uuid]) }}" class="btn btn-info">Details</a>
                                @if($configuration->repositories)
                                    <a href="{{ $configuration->homepage }}" target="_blank" class="btn btn-info">Homepage</a>
                                @endif
                                <a href="{{ route('satis.configuration.edit', ['uuid' => $configuration->uuid]) }}" class="btn btn-info">Edit</a>
                                @if($configuration->repositories)
                                    {{ Form::open(['url' => route('satis.configuration.build', ['uuid' => $configuration->uuid]), 'class' => 'd-inline']) }}
                                        <button type="submit" class="btn btn-info">Build</button>
                                    {{ Form::close() }}
                                    {{ Form::open(['url' => route('satis.configuration.purge', ['uuid' => $configuration->uuid]), 'class' => 'd-inline']) }}
                                        <button type="submit" class="btn btn-danger" data-confirm="Are you sure you want to purge »{{ $configuration->name }}«?">Purge</button>
                                    {{ Form::close() }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>

                {{ $configurations->links() }}
            @else
                No configurations defined.
                <a href="{{ route('satis.configuration.create') }}">Create one now.</a>
            @endif
        </div>
    </section>
@endsection
