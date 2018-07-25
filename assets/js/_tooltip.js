/**
 *    Tooltip
 *
 *    @tableofcontent
 *      1. Dependencies
 *      2. Functions
 *
 */

/**
 *     @section 1. Dependencies
 */

const tippy = require('tippy.js/dist/tippy.standalone.js');

/**
 *     @section 2. Functions
 */

$(function() {
    tippy('[title]', {
        arrow: true,
        arrowType: 'round',
        size: 'small'
    });
});

// end of _tooltip.js
