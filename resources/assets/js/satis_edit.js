'use strict';

let ajv = new Ajv({
    meta: false,
    extendRefs: true,
    unknownFormats: 'ignore',
    allErrors: true,
    verbose: true
});

ajv.addMetaSchema(jsoneditor.meta);
ajv._opts.defaultMeta = jsoneditor.meta.id;
ajv.removeKeyword('propertyNames');
ajv.removeKeyword('contains');
ajv.removeKeyword('const');

let editor = new JSONEditor(
    document.getElementById('configuration-jsoneditor'),
    {
        mode: 'code',
        modes: ['code', 'tree'],
        ajv: ajv,
        search: false,
        schema: jsoneditor.schema,
        templates: jsoneditor.templates,
        onError: function (err) {
            alert(err.toString());
        },
        onModeChange: function (newMode, oldMode) {
            if (newMode === 'tree') {
                editor.expandAll();
            }
        }
    },
    jsoneditor.config
);

$('#jsoneditor-form').on({
    'submit': function () {
        $('#configuration').val(JSON.stringify(editor.get()));
    }
});

let setCrontabHint = function (crontab) {
    if (crontab.trim()) {
        $('#crontab_wrapper').show();
        $('#crontab_human_readable').text(prettyCron.toString(crontab, false));
        $('#crontab_next').text('next ' + prettyCron.getNext(crontab));
    } else {
        $('#crontab_wrapper').hide();
        $('#crontab_human_readable').text('');
        $('#crontab_next').text('');
    }
};

let crontab = $('#crontab');
crontab.on('blur change input keyup paste', function (event) {
    setCrontabHint(event.target.value);
});
setCrontabHint(crontab.val());
