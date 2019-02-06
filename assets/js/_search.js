/**
 *    Search
 *
 *    @tableofcontent
 *      1. Functions
 *       1.1 Init
 *       1.2 Event Listener
 *
 */

/**
 *     @section 1. Functions
 */

$(function() {

    $('#depmon-filter input').change(function() {
        $('#depmon-filter').submit();
    });

    $('#depmon-filter').submit(function (e) {
        e.preventDefault();

        $('#depmon-list').before('<div class="loading animated">\n' +
            '            <div class="square square-1 size-m color-blue"></div>\n' +
            '            <div class="square square-2 size-m color-green"></div>\n' +
            '            <div class="square square-3 size-s color-yellow"></div>\n' +
            '            <div class="square square-4 size-s color-green"></div>\n' +
            '            <div class="square square-5 size-m color-red"></div>\n' +
            '            <div class="square square-6 size-m color-yellow"></div>\n' +
            '            <div class="square square-7 size-s color-blue"></div>\n' +
            '            <div class="square square-8 size-s color-red"></div>\n' +
            '        </div>');
        $('#depmon-list').addClass('overlay');

        $.ajax({
            url: '/ajax',
            type: 'POST',
            data: $('#depmon-filter').serialize(),
            async: true,
            cache: false,
            dataType: 'html'
        }).done(function (response) {
            $('#depmon-list').html(response);
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
            $('#depmon-list').removeClass('overlay');
        });
    });
    // @section 1.1 Init
    // var filter = Filter.init({
    //     filterForms: [
    //         '#depmon-filter'
    //     ],
    //     list: '#depmon-list',
    //     storage: true,
    //     afterApplyFilter: function () {
    //         // hide list header with no more entries in the list
    //         $('.list-header').removeClass('hidden');
    //         if (!$('#depmon-filter input[name="overview"]:checked').length) {
    //             $('#depmon-list ul:not(.header)').each(function () {
    //                 var list = $(this);
    //                 if (list.find('li:not(.hidden):not(.list-header)') == null || list.find('li:not(.hidden):not(.list-header)').length === 0) {
    //                     var listHeader = $(list.find('li.list-header'))[0];
    //                     $(listHeader).addClass('hidden');
    //                 }
    //             });
    //         }
    //     },
    //     debug: true
    // });
    //
    // // @section 1.2 Event Listener
    // $('.filter-reset').click(function () {
    //     filter.reset();
    //     filter.applyFilter(null);
    //     $('.collapse').collapse('hide');
    // });
    //
    // // ToDo: Combine these event listener to a more generic one
    // // Quick filter for project
    // $('.filter-project').click(function() {
    //     var project = $(this).attr('data-project');
    //     var object = {
    //         'project': [
    //             project
    //         ]
    //     };
    //     filter.applyFilter(object);
    //     $('#filter-project-collapse').collapse('show');
    // });
    //
    // // Quick filter for dependency
    // $('.filter-dependency').click(function() {
    //     var dependency = $(this).attr('data-dependency');
    //     var object = {
    //         'name': [
    //             dependency
    //         ]
    //     };
    //     filter.applyFilter(object);
    // });
    //
    // // Quick filter for type
    // $('.filter-type').click(function() {
    //     var type = $(this).attr('data-type');
    //     var object = {
    //         'project-type': [
    //             type
    //         ]
    //     };
    //     filter.applyFilter(object);
    //     $('#filter-type-collapse').collapse('show');
    // });
    //
    // // Quick filter for state and the project
    // $('.filter-state-project').click(function() {
    //     var project = $(this).attr('data-project');
    //     var state = $(this).attr('data-state');
    //     var object = {
    //         'project': [
    //             project
    //         ],
    //         'state': [
    //             state
    //         ]
    //     };
    //     filter.applyFilter(object);
    //     $('#filter-project-collapse').collapse('show');
    //     $('#filter-state-collapse').collapse('show');
    // });
    //
    // // Quick filter for type
    // $('.filter-required-project').click(function() {
    //     var project = $(this).attr('data-project');
    //     var required = $(this).attr('data-required');
    //     var object = {
    //         'project': [
    //             project
    //         ],
    //         'required': [
    //             required
    //         ]
    //     };
    //     filter.applyFilter(object);
    //     $('#filter-project-collapse').collapse('show');
    // });
});

// end of _search.js
