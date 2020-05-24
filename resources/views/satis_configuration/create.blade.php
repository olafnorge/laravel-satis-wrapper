@extends('layouts.app')

@section('content')
    {{ Form::open([
            'method' => 'post',
            'title' => 'Satis Configuration' . (request()->route('uuid') ? sprintf(' »%s«', $record->name) : ''),
            'route' => request()->route('uuid')
                ? [request()->route()->getName(), ['uuid' => request()->route('uuid')]]
                : request()->route()->getName(),
            'id' => 'jsoneditor-form',
    ]) }}
        @include('elements.satis_create_configuration_form')
        {{ Form::hidden('configuration', null, ['id' => 'configuration']) }}

        <h5 class="card-title mt-4">Secured with password</h5>
        <div class="form-check form-check-inline">
            {{ Form::checkbox('password_secured', 1, $record->password_secured === null ? true : (bool)$record->password_secured, ['class' => 'form-check-input', 'id' => 'password_secured']) }}
            <label class="form-check-label" for="password_secured">Enable password protection</label>
        </div>

        <h5 class="card-title mt-4">Crontab</h5>
        <div class="form-group">
            <input type="text" class="form-control" value="{{ $record->crontab }}" name="crontab" id="crontab">
        </div>
        <p class="text-right small hide" id="crontab_wrapper"><em><span id="crontab_human_readable"></span> - <span id="crontab_next"></span></em></p>

    {{ Form::close([
        '<a href="' . route('satis.configuration.index') .'" class="btn btn-info">Back</a>',
        Form::submit('Save', ['class' => 'btn btn-primary float-right'])
    ]) }}
@endsection


