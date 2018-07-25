/**
 *    Update
 *
 *    @tableofcontent
 *      1. Functions
 *
 */

/**
 *     @section 1. Functions
 */


$(function() {
    $('.filter-update').click(function () {

        $(this).before('<div class="loading animated">\n' +
            '            <div class="square square-1 size-m color-blue"></div>\n' +
            '            <div class="square square-2 size-m color-green"></div>\n' +
            '            <div class="square square-3 size-s color-yellow"></div>\n' +
            '            <div class="square square-4 size-s color-green"></div>\n' +
            '            <div class="square square-5 size-m color-red"></div>\n' +
            '            <div class="square square-6 size-m color-yellow"></div>\n' +
            '            <div class="square square-7 size-s color-blue"></div>\n' +
            '            <div class="square square-8 size-s color-red"></div>\n' +
            '        </div>');
        $.ajax({
            url: $('.filter-update-target').val(),
            type: 'GET',
            async: true,
            cache: false,
            dataType: 'json'
        }).done(function (response) {
            console.log(response);
            $('.filter-update').after('<div class="flash-message__wrapper">\n' +
                '  <div class="flash-message success top right">\n' +
                '  The projects were successfully updated.\n' +
                '</div></div>');
        }).fail(function (jqXHR, textStatus, error) {
            console.log("Post error: " + error);
            $('.filter-update').after('<div class="flash-message__wrapper">\n' +
                '  <div class="flash-message alert top right">\n' +
                '  Ooops! Something went wrong.\n' +
                '</div></div>');
        }).always(function () {
            $('.loading').fadeOut(function() {
                $(this).remove();
            });
        });
    });
});

// end of _update.js
