@push('css')
    {{ Html::style(mix('css/jsoneditor.css')) }}
@endpush

@push('js')
    {{ Html::script(mix('js/jsoneditor.js')) }}
    {{ Html::script(mix('js/prettycron.js')) }}

    <script>
        let jsoneditor = {
            "config": {!! $record->configuration !!},
            "meta": {!! $metaSchema !!},
            "schema": {!! $schema !!},
            "templates": {!! $templates !!}
        };
    </script>

    {{ Html::script(mix('js/satis_edit.js')) }}
@endpush

<p>Use the editor to configure the satis configuration. The json will be validated automatically.</p>
<p>
    The editor provides you with templates of all available settings and options.
    Please refer to <a href="https://github.com/composer/satis/blob/master/res/satis-schema.json" target="_blank">the
    satis schema</a> in order to read the documentation of all available settings and options.
</p>
<p>
    Please also read
    <a href="https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md" target="_blank">
        the satis documentation
    </a>
    in order to get familiar with the available options.
</p>
<p>
    Changes to the following attributes will always be overwritten automatically on safe in order to keep data integrity
    and sanity.

    <ul>
        <li>
            homepage &rarr; always transforms to
            <code>
                @if(request()->routeIs('satis.configuration.edit'))
                    {{ $record->homepage }}
                @else
                    {{ generate_satis_homepage('slugged-name-of-repository') }}
                @endif
            </code>
        </li>
        <li>
            output-dir &rarr; always transforms to
            <code>
                @if(request()->routeIs('satis.configuration.edit'))
                    {{ json_decode($record->configuration, true)['output-dir'] }}
                @else
                    {{ rtrim(config('satis.output_dir'), DIRECTORY_SEPARATOR) }}/&lt;uuid&gt;
                @endif
            </code>
        </li>
        <li>archive.directory &rarr; always transforms to <code>dist</code></li>
        <li>
            archive.absolute-directory &rarr; always gets removed
        </li>
    </ul>
</p>

@if($errors->has('configuration'))
    <div class="bs-callout bs-callout-danger">
        <h5 class="card-title">{{{ session()->get('error') }}}</h5>
        <ul>
            @foreach($errors->get('configuration') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div id="configuration-jsoneditor"></div>
