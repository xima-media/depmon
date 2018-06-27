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

    $('.filter-reset').click(function () {
        filter.reset();
        filter.applyFilter(null);
        $('.collapse').collapse('hide');
    });

    // ToDo: Combine these event listener to a more generic one
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

    // Quick filter for dependency
    $('.filter-dependency').click(function() {
        var dependency = $(this).attr('data-dependency');
        var object = {
            'name': [
                dependency
            ]
        };
        filter.applyFilter(object);
    });

    // Quick filter for type
    $('.filter-type').click(function() {
        var type = $(this).attr('data-type');
        var object = {
            'project-type': [
                type
            ]
        };
        filter.applyFilter(object);
        $('#filter-type-collapse').collapse('show');
    });

    // Quick filter for state and the project
    $('.filter-state-project').click(function() {
        var project = $(this).attr('data-project');
        var state = $(this).attr('data-state');
        var object = {
            'project': [
                project
            ],
            'state': [
                state
            ]
        };
        filter.applyFilter(object);
        $('#filter-project-collapse').collapse('show');
        $('#filter-state-collapse').collapse('show');
    });

    // Quick filter for type
    $('.filter-required-project').click(function() {
        var project = $(this).attr('data-project');
        var required = $(this).attr('data-required');
        var object = {
            'project': [
                project
            ],
            'required': [
                required
            ]
        };
        filter.applyFilter(object);
        $('#filter-project-collapse').collapse('show');
    });
});