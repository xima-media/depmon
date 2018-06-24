$(function() {
    var filter = Filter.init({
        filterForms: [
            '#depmon-filter'
        ],
        list: '#depmon-list',
        storage: true,
        afterApplyFilter: function () {
            // hide list header with no more entries in the list
            $('.list-header').removeClass('hidden');
            if (!$('#depmon-filter input[name="overview"]:checked').length) {
                $('#depmon-list ul:not(.header)').each(function () {
                    var list = $(this);
                    if (list.find('li:not(.hidden):not(.list-header)').length === 0) {
                        var listHeader = $(list.find('li.list-header'))[0];
                        $(listHeader).addClass('hidden');
                    }
                });
            }
        },
        debug: true
    });

    // Quick filter for project
    $('.filter-project').click(function() {
        var project = $(this).attr('data-project');
        var object = {
            'project': [
                project
            ]
        };
        filter.applyFilter(object);
        $('#filter-project-collapse').collapse('show');
    });

    $('.filter-reset').click(function () {
        filter.reset();
        filter.applyFilter(null);
        $('.collapse').collapse('hide');
    });
});