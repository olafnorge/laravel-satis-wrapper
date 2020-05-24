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

// page wide functions
$('[data-toggle="popover"]').popover();
$('.popover-dismiss').popover({
    trigger: 'focus'
});

// tooltips
$('[data-toggle="tooltip"]').tooltip();

// transform links to post
$('.do-post').on('click', function (event) {
    event.preventDefault();

    if ($(this).data('confirm')) {
        if ($(this).data('formid') && confirm($(this).data('confirm'))) {
            $('#' + $(this).data('formid')).submit();
        }
    } else if ($(this).data('formid')) {
        $('#' + $(this).data('formid')).submit();
    }
});

// fade out flash messages
window.setTimeout(function () {
    $(".alert").not('.alert-danger').alert('close')
}, 5000);
