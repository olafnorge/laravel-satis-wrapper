'use strict';

window.ClipboardJS = require('clipboard');

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

new JSONEditor(
    document.getElementById('configuration-jsoneditor'),
    {
        mode: 'code',
        ajv: ajv,
        schema: jsoneditor.schema,
        onEditable: function (node) {
            if (!node.path) {
                // In modes code and text, node is empty: no path, field, or value
                // returning false makes the text area read-only
                return false;
            }
        },
        onError: function (err) {
            alert(err.toString());
        }
    },
    jsoneditor.config
);

let clipboard = new ClipboardJS('.btn-clipboard');
clipboard.on('success', function (e) {
    e.clearSelection();
    let title = 'Copy to clipboard';
    $(e.trigger).tooltip('hide')
        .attr('data-original-title', 'Copied!')
        .tooltip('show');
    setTimeout(function() {
        $(e.trigger).tooltip('hide').attr('data-original-title', title);
    }, 1500);
});

if (jsoneditor.crontab) {
    $('#crontab_human_readable').text(prettyCron.toString(jsoneditor.crontab));
    $('#crontab_next').text('next ' + prettyCron.getNext(jsoneditor.crontab));
}
