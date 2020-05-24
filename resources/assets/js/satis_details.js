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

if (jsoneditor.crontab) {
    $('#crontab_human_readable').text(prettyCron.toString(jsoneditor.crontab));
    $('#crontab_next').text('next ' + prettyCron.getNext(jsoneditor.crontab));
}
