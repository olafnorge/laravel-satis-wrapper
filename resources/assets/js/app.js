/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

// extend jQuery with a regex selector
$.expr[':'].regex = function (elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ?
                matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels, '')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g, ''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
};

// always send CSRF token with each ajax request
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();
    $('[data-toggle="tooltip"]').tooltip();

    // do logout via POST
    $('#logout-button').on('click', function (event) {
        event.preventDefault();
        $('#logout-form').submit();
    });

    // confirm dialog for destructive actions
    $('*[data-confirm]').click(function (event) {
        if (!confirm($(this).data('confirm'))) {
            event.preventDefault();
        }
    });

    // fade out flash messages
    window.setTimeout(function () {
        $(".alert").not('.alert-danger').alert('close')
    }, 5000);
});
