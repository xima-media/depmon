webpackJsonp([1],{

/***/ "./assets/js/app.js":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_bootstrap_sass_assets_javascripts_bootstrap_transition_js__ = __webpack_require__("./node_modules/bootstrap-sass/assets/javascripts/bootstrap/transition.js");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_bootstrap_sass_assets_javascripts_bootstrap_transition_js___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_bootstrap_sass_assets_javascripts_bootstrap_transition_js__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_bootstrap_sass_assets_javascripts_bootstrap_collapse_js__ = __webpack_require__("./node_modules/bootstrap-sass/assets/javascripts/bootstrap/collapse.js");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_bootstrap_sass_assets_javascripts_bootstrap_collapse_js___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_bootstrap_sass_assets_javascripts_bootstrap_collapse_js__);
// loads the Bootstrap jQuery plugins



/***/ }),

/***/ "./assets/js/filter.js":
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(jQuery) {/**
 * filter.js
 *
 * @author Konrad Michalik <konrad.michalik@xima.de>
 * @version 1.0.0
 * @depends
 *        jQuery 1.4.1
 *
 */
Filter = function (window, document, $, undefined) {

    var EXT = {};
    var _self = this;

    var _namespace = 'filter.js';

    var _options = {
        // array of all filter forms, which will be considered by the filtering process
        filterForms: [],
        // list to be filtered
        list: null,
        // use session storage for data handling to speed up the filter process
        storage: false,
        // css class to hide the unmatched list entries
        hideClass: 'hidden',
        // elements which should be filtered
        filterableElements: 'li',
        // start filtering on input or on form submit
        filterOnSubmit: false,
        // callback function before list is filtered
        beforeApplyFilter: function beforeApplyFilter() {},
        // callback function after list is filtered
        afterApplyFilter: function afterApplyFilter() {},
        // enable debug output in console
        debug: false
    };

    var _elements = [];

    /*
     * Initialize filter.js
     *
     * @return this
     * @param parameters
     */
    EXT.init = function (parameters) {

        // load additional parameters
        if (parameters) {
            jQuery.extend(_options, parameters);
        }

        _options.debug ? console.log('[' + _namespace + '] filter.js initialized') : '';

        _self.initEventListener();

        // check for storage option
        if (_options.storage) {
            if (typeof Storage !== 'undefined') {
                _self.initStorage();
            } else {
                throw '[' + _namespace + '] no webstorage support!';
            }
        }

        return this;
    };

    /**
     * Clear session storage
     */
    EXT.clearStorage = function () {
        _elements = [];
        sessionStorage.clear();
    };

    /**
     * Initialize event listener
     */
    this.initEventListener = function () {

        _options.debug ? console.log('[' + _namespace + '] init event listener') : '';

        $.each(_options.filterForms, function (index, form) {
            if ($(form).length) {
                var _form = $(form);
                _form.unbind();

                if (_options.filterOnSubmit) {
                    _form.on('submit', function (event) {
                        event.preventDefault();
                        _options.debug ? console.log('[' + _namespace + '] filter submit') : '';
                        EXT.applyFilter();
                    });
                } else {
                    _form.find('input').on('input', function (event) {
                        _options.debug ? console.log('[' + _namespace + '] filter change') : '';
                        EXT.applyFilter();
                    });

                    _form.on('submit', function (event) {
                        event.preventDefault();
                    });
                }
            }
        });
    };

    /**
     * Initialize session storage while parsing list data
     */
    this.initStorage = function () {

        EXT.clearStorage();
        var _list = $(_options.list);
        var _entries = _list.find(_options.filterableElements);

        _options.debug ? console.log('[' + _namespace + '] init storage') : '';

        $.each(_entries, function (num, entry) {
            var _entry = $(this);
            var _element = {};

            $.each(_entry.get(0).attributes, function (i, attrib) {
                if (attrib.name.indexOf('data-') !== -1) {
                    var _name = attrib.name.replace('data-', '');
                    _element[_name] = attrib.value;
                }
            });

            _elements.push(num);
            sessionStorage.setItem(num, JSON.stringify(_element));
        });
    };

    /**
     * Build the filter object
     */
    this.buildFilter = function () {

        var _filter = {};

        _options.beforeApplyFilter();

        $.each(_options.filterForms, function (index, form) {
            if ($(form).length) {
                var _form = $(form);
                var _formData = _form.serializeArray();

                $.each(_formData, function (key, value) {
                    if (value['value'] !== '') {
                        if (_filter[value['name']] !== undefined) {
                            var _object = _filter[value['name']];
                        } else {
                            var _object = {};
                        }
                        _object[Object.keys(_object).length] = value['value'];
                        _filter[value['name']] = _object;
                    }
                });
            }
        });

        return _filter;
    };

    /**
     * Apply the filter settings to the given list
     */
    EXT.applyFilter = function () {
        var _filter = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

        if (_filter === null) {
            _filter = _self.buildFilter();
        } else {
            EXT.reset();
            _self.synchronizeFilterForm(_filter);
        }

        var _list = $(_options.list);
        var _entries = _list.find(_options.filterableElements);

        if (_options.storage) {
            $.each(_elements, function (num, entry) {
                var _state = [];
                var _element = JSON.parse(sessionStorage.getItem(entry));

                $.each(_filter, function (key, value) {
                    if (_element[key] !== undefined) {
                        // ToDo: restrict only on given forms
                        if ($('input[name="' + key + '"]').attr('type') === 'text') {
                            _state.push(_element[key].indexOf(value[0]) >= 0);
                        } else {
                            _state.push(jQuery.inArray(_element[key], Object.values(value)) !== -1);
                        }
                    }
                });

                if (jQuery.inArray(false, _state) !== -1) {
                    $(_entries[num]).addClass(_options.hideClass);
                } else {
                    $(_entries[num]).removeClass(_options.hideClass);
                }
            });
        } else {
            $.each(_entries, function (num, entry) {
                var _state = [];
                var _entry = $(this);

                $.each(_filter, function (key, value) {
                    if (_entry.attr('data-' + key)) {
                        // ToDo: restrict only on given forms
                        if ($('input[name="' + key + '"]').attr('type') === 'text') {
                            _state.push(_entry.attr('data-' + key).indexOf(value[0]) >= 0);
                        } else {
                            _state.push(jQuery.inArray(_entry.attr('data-' + key), Object.values(value)) !== -1);
                        }
                    }
                });

                if (jQuery.inArray(false, _state) !== -1) {
                    _entry.addClass(_options.hideClass);
                } else {
                    _entry.removeClass(_options.hideClass);
                }
            });
        }

        _options.afterApplyFilter();
    };

    /**
     * Get a given filter and synchronize the filter form
     * @param _filter
     */
    this.synchronizeFilterForm = function (_filter) {

        $.each(_filter, function (key, value) {
            // ToDo: restrict only on given forms
            if ($('input[name="' + key + '"]').attr('type') === 'text') {
                $('input[name="' + key + '"]').val(value);
            } else {
                $('input[name="' + key + '"][value="' + value + '"]').prop('checked', true);
            }
        });
    };

    /**
     * Reset the filter form
     */
    EXT.reset = function () {
        $.each(_options.filterForms, function (index, form) {
            if ($(form).length) {
                var _form = $(form);
                _form.unbind();
                _form.find('input').each(function () {
                    if ($(this).attr('type') === 'text') {
                        $(this).val('');
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            }
        });
    };

    return EXT;
}(window, document, jQuery);
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__("./node_modules/jquery/dist/jquery.js")))

/***/ }),

/***/ "./assets/js/search.js":
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {$(function () {
    var filter = Filter.init({
        filterForms: ['#depmon-filter'],
        list: '#depmon-list',
        storage: true,
        afterApplyFilter: function afterApplyFilter() {
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
    $('.filter-project').click(function () {
        var project = $(this).attr('data-project');
        var object = {
            'project': [project]
        };
        filter.applyFilter(object);
        $('#filter-project-collapse').collapse('show');
    });

    // Quick filter for dependency
    $('.filter-dependency').click(function () {
        var dependency = $(this).attr('data-dependency');
        var object = {
            'name': [dependency]
        };
        filter.applyFilter(object);
    });

    // Quick filter for type
    $('.filter-type').click(function () {
        var type = $(this).attr('data-type');
        var object = {
            'project-type': [type]
        };
        filter.applyFilter(object);
        $('#filter-type-collapse').collapse('show');
    });

    // Quick filter for state and the project
    $('.filter-state-project').click(function () {
        var project = $(this).attr('data-project');
        var state = $(this).attr('data-state');
        var object = {
            'project': [project],
            'state': [state]
        };
        filter.applyFilter(object);
        $('#filter-project-collapse').collapse('show');
        $('#filter-state-collapse').collapse('show');
    });

    // Quick filter for type
    $('.filter-required-project').click(function () {
        var project = $(this).attr('data-project');
        var required = $(this).attr('data-required');
        var object = {
            'project': [project],
            'required': [required]
        };
        filter.applyFilter(object);
        $('#filter-project-collapse').collapse('show');
    });
});
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__("./node_modules/jquery/dist/jquery.js")))

/***/ }),

/***/ "./node_modules/bootstrap-sass/assets/javascripts/bootstrap/collapse.js":
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(jQuery) {/* ========================================================================
 * Bootstrap: collapse.js v3.3.7
 * http://getbootstrap.com/javascript/#collapse
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

/* jshint latedef: false */

+function ($) {
  'use strict';

  // COLLAPSE PUBLIC CLASS DEFINITION
  // ================================

  var Collapse = function (element, options) {
    this.$element      = $(element)
    this.options       = $.extend({}, Collapse.DEFAULTS, options)
    this.$trigger      = $('[data-toggle="collapse"][href="#' + element.id + '"],' +
                           '[data-toggle="collapse"][data-target="#' + element.id + '"]')
    this.transitioning = null

    if (this.options.parent) {
      this.$parent = this.getParent()
    } else {
      this.addAriaAndCollapsedClass(this.$element, this.$trigger)
    }

    if (this.options.toggle) this.toggle()
  }

  Collapse.VERSION  = '3.3.7'

  Collapse.TRANSITION_DURATION = 350

  Collapse.DEFAULTS = {
    toggle: true
  }

  Collapse.prototype.dimension = function () {
    var hasWidth = this.$element.hasClass('width')
    return hasWidth ? 'width' : 'height'
  }

  Collapse.prototype.show = function () {
    if (this.transitioning || this.$element.hasClass('in')) return

    var activesData
    var actives = this.$parent && this.$parent.children('.panel').children('.in, .collapsing')

    if (actives && actives.length) {
      activesData = actives.data('bs.collapse')
      if (activesData && activesData.transitioning) return
    }

    var startEvent = $.Event('show.bs.collapse')
    this.$element.trigger(startEvent)
    if (startEvent.isDefaultPrevented()) return

    if (actives && actives.length) {
      Plugin.call(actives, 'hide')
      activesData || actives.data('bs.collapse', null)
    }

    var dimension = this.dimension()

    this.$element
      .removeClass('collapse')
      .addClass('collapsing')[dimension](0)
      .attr('aria-expanded', true)

    this.$trigger
      .removeClass('collapsed')
      .attr('aria-expanded', true)

    this.transitioning = 1

    var complete = function () {
      this.$element
        .removeClass('collapsing')
        .addClass('collapse in')[dimension]('')
      this.transitioning = 0
      this.$element
        .trigger('shown.bs.collapse')
    }

    if (!$.support.transition) return complete.call(this)

    var scrollSize = $.camelCase(['scroll', dimension].join('-'))

    this.$element
      .one('bsTransitionEnd', $.proxy(complete, this))
      .emulateTransitionEnd(Collapse.TRANSITION_DURATION)[dimension](this.$element[0][scrollSize])
  }

  Collapse.prototype.hide = function () {
    if (this.transitioning || !this.$element.hasClass('in')) return

    var startEvent = $.Event('hide.bs.collapse')
    this.$element.trigger(startEvent)
    if (startEvent.isDefaultPrevented()) return

    var dimension = this.dimension()

    this.$element[dimension](this.$element[dimension]())[0].offsetHeight

    this.$element
      .addClass('collapsing')
      .removeClass('collapse in')
      .attr('aria-expanded', false)

    this.$trigger
      .addClass('collapsed')
      .attr('aria-expanded', false)

    this.transitioning = 1

    var complete = function () {
      this.transitioning = 0
      this.$element
        .removeClass('collapsing')
        .addClass('collapse')
        .trigger('hidden.bs.collapse')
    }

    if (!$.support.transition) return complete.call(this)

    this.$element
      [dimension](0)
      .one('bsTransitionEnd', $.proxy(complete, this))
      .emulateTransitionEnd(Collapse.TRANSITION_DURATION)
  }

  Collapse.prototype.toggle = function () {
    this[this.$element.hasClass('in') ? 'hide' : 'show']()
  }

  Collapse.prototype.getParent = function () {
    return $(this.options.parent)
      .find('[data-toggle="collapse"][data-parent="' + this.options.parent + '"]')
      .each($.proxy(function (i, element) {
        var $element = $(element)
        this.addAriaAndCollapsedClass(getTargetFromTrigger($element), $element)
      }, this))
      .end()
  }

  Collapse.prototype.addAriaAndCollapsedClass = function ($element, $trigger) {
    var isOpen = $element.hasClass('in')

    $element.attr('aria-expanded', isOpen)
    $trigger
      .toggleClass('collapsed', !isOpen)
      .attr('aria-expanded', isOpen)
  }

  function getTargetFromTrigger($trigger) {
    var href
    var target = $trigger.attr('data-target')
      || (href = $trigger.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') // strip for ie7

    return $(target)
  }


  // COLLAPSE PLUGIN DEFINITION
  // ==========================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.collapse')
      var options = $.extend({}, Collapse.DEFAULTS, $this.data(), typeof option == 'object' && option)

      if (!data && options.toggle && /show|hide/.test(option)) options.toggle = false
      if (!data) $this.data('bs.collapse', (data = new Collapse(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.collapse

  $.fn.collapse             = Plugin
  $.fn.collapse.Constructor = Collapse


  // COLLAPSE NO CONFLICT
  // ====================

  $.fn.collapse.noConflict = function () {
    $.fn.collapse = old
    return this
  }


  // COLLAPSE DATA-API
  // =================

  $(document).on('click.bs.collapse.data-api', '[data-toggle="collapse"]', function (e) {
    var $this   = $(this)

    if (!$this.attr('data-target')) e.preventDefault()

    var $target = getTargetFromTrigger($this)
    var data    = $target.data('bs.collapse')
    var option  = data ? 'toggle' : $this.data()

    Plugin.call($target, option)
  })

}(jQuery);

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__("./node_modules/jquery/dist/jquery.js")))

/***/ }),

/***/ "./node_modules/bootstrap-sass/assets/javascripts/bootstrap/transition.js":
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(jQuery) {/* ========================================================================
 * Bootstrap: transition.js v3.3.7
 * http://getbootstrap.com/javascript/#transitions
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // CSS TRANSITION SUPPORT (Shoutout: http://www.modernizr.com/)
  // ============================================================

  function transitionEnd() {
    var el = document.createElement('bootstrap')

    var transEndEventNames = {
      WebkitTransition : 'webkitTransitionEnd',
      MozTransition    : 'transitionend',
      OTransition      : 'oTransitionEnd otransitionend',
      transition       : 'transitionend'
    }

    for (var name in transEndEventNames) {
      if (el.style[name] !== undefined) {
        return { end: transEndEventNames[name] }
      }
    }

    return false // explicit for ie8 (  ._.)
  }

  // http://blog.alexmaccaw.com/css-transitions
  $.fn.emulateTransitionEnd = function (duration) {
    var called = false
    var $el = this
    $(this).one('bsTransitionEnd', function () { called = true })
    var callback = function () { if (!called) $($el).trigger($.support.transition.end) }
    setTimeout(callback, duration)
    return this
  }

  $(function () {
    $.support.transition = transitionEnd()

    if (!$.support.transition) return

    $.event.special.bsTransitionEnd = {
      bindType: $.support.transition.end,
      delegateType: $.support.transition.end,
      handle: function (e) {
        if ($(e.target).is(this)) return e.handleObj.handler.apply(this, arguments)
      }
    }
  })

}(jQuery);

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__("./node_modules/jquery/dist/jquery.js")))

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("./assets/js/app.js");
__webpack_require__("./assets/js/filter.js");
module.exports = __webpack_require__("./assets/js/search.js");


/***/ })

},[0]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvYXBwLmpzIiwid2VicGFjazovLy8uL2Fzc2V0cy9qcy9maWx0ZXIuanMiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2pzL3NlYXJjaC5qcyIsIndlYnBhY2s6Ly8vLi9+L2Jvb3RzdHJhcC1zYXNzL2Fzc2V0cy9qYXZhc2NyaXB0cy9ib290c3RyYXAvY29sbGFwc2UuanMiLCJ3ZWJwYWNrOi8vLy4vfi9ib290c3RyYXAtc2Fzcy9hc3NldHMvamF2YXNjcmlwdHMvYm9vdHN0cmFwL3RyYW5zaXRpb24uanMiXSwibmFtZXMiOlsiRmlsdGVyIiwid2luZG93IiwiZG9jdW1lbnQiLCIkIiwidW5kZWZpbmVkIiwiRVhUIiwiX3NlbGYiLCJfbmFtZXNwYWNlIiwiX29wdGlvbnMiLCJmaWx0ZXJGb3JtcyIsImxpc3QiLCJzdG9yYWdlIiwiaGlkZUNsYXNzIiwiZmlsdGVyYWJsZUVsZW1lbnRzIiwiZmlsdGVyT25TdWJtaXQiLCJiZWZvcmVBcHBseUZpbHRlciIsImFmdGVyQXBwbHlGaWx0ZXIiLCJkZWJ1ZyIsIl9lbGVtZW50cyIsImluaXQiLCJwYXJhbWV0ZXJzIiwialF1ZXJ5IiwiZXh0ZW5kIiwiY29uc29sZSIsImxvZyIsImluaXRFdmVudExpc3RlbmVyIiwiU3RvcmFnZSIsImluaXRTdG9yYWdlIiwiY2xlYXJTdG9yYWdlIiwic2Vzc2lvblN0b3JhZ2UiLCJjbGVhciIsImVhY2giLCJpbmRleCIsImZvcm0iLCJsZW5ndGgiLCJfZm9ybSIsInVuYmluZCIsIm9uIiwiZXZlbnQiLCJwcmV2ZW50RGVmYXVsdCIsImFwcGx5RmlsdGVyIiwiZmluZCIsIl9saXN0IiwiX2VudHJpZXMiLCJudW0iLCJlbnRyeSIsIl9lbnRyeSIsIl9lbGVtZW50IiwiZ2V0IiwiYXR0cmlidXRlcyIsImkiLCJhdHRyaWIiLCJuYW1lIiwiaW5kZXhPZiIsIl9uYW1lIiwicmVwbGFjZSIsInZhbHVlIiwicHVzaCIsInNldEl0ZW0iLCJKU09OIiwic3RyaW5naWZ5IiwiYnVpbGRGaWx0ZXIiLCJfZmlsdGVyIiwiX2Zvcm1EYXRhIiwic2VyaWFsaXplQXJyYXkiLCJrZXkiLCJfb2JqZWN0IiwiT2JqZWN0Iiwia2V5cyIsInJlc2V0Iiwic3luY2hyb25pemVGaWx0ZXJGb3JtIiwiX3N0YXRlIiwicGFyc2UiLCJnZXRJdGVtIiwiYXR0ciIsImluQXJyYXkiLCJ2YWx1ZXMiLCJhZGRDbGFzcyIsInJlbW92ZUNsYXNzIiwidmFsIiwicHJvcCIsImZpbHRlciIsImxpc3RIZWFkZXIiLCJjbGljayIsImNvbGxhcHNlIiwicHJvamVjdCIsIm9iamVjdCIsImRlcGVuZGVuY3kiLCJ0eXBlIiwic3RhdGUiLCJyZXF1aXJlZCJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7O0FBQUE7QUFBQTtBQUFBO0FBQ0E7Ozs7Ozs7O0FDREE7Ozs7Ozs7OztBQVNBQSxTQUFVLFVBQVVDLE1BQVYsRUFBa0JDLFFBQWxCLEVBQTRCQyxDQUE1QixFQUErQkMsU0FBL0IsRUFBMEM7O0FBRWhELFFBQUlDLE1BQU0sRUFBVjtBQUNBLFFBQUlDLFFBQVEsSUFBWjs7QUFHQSxRQUFJQyxhQUFhLFdBQWpCOztBQUVBLFFBQUlDLFdBQVc7QUFDWDtBQUNBQyxxQkFBYSxFQUZGO0FBR1g7QUFDQUMsY0FBTSxJQUpLO0FBS1g7QUFDQUMsaUJBQVMsS0FORTtBQU9YO0FBQ0FDLG1CQUFXLFFBUkE7QUFTWDtBQUNBQyw0QkFBb0IsSUFWVDtBQVdYO0FBQ0FDLHdCQUFnQixLQVpMO0FBYVg7QUFDQUMsMkJBQW1CLDZCQUFZLENBRTlCLENBaEJVO0FBaUJYO0FBQ0FDLDBCQUFrQiw0QkFBWSxDQUU3QixDQXBCVTtBQXFCWDtBQUNBQyxlQUFPO0FBdEJJLEtBQWY7O0FBeUJBLFFBQUlDLFlBQVksRUFBaEI7O0FBRUE7Ozs7OztBQU1BYixRQUFJYyxJQUFKLEdBQVcsVUFBVUMsVUFBVixFQUFzQjs7QUFFN0I7QUFDQSxZQUFJQSxVQUFKLEVBQWdCO0FBQ1pDLG1CQUFPQyxNQUFQLENBQWNkLFFBQWQsRUFBd0JZ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IHRoZSBCb290c3RyYXAgalF1ZXJ5IHBsdWdpbnNcbmltcG9ydCAnYm9vdHN0cmFwLXNhc3MvYXNzZXRzL2phdmFzY3JpcHRzL2Jvb3RzdHJhcC90cmFuc2l0aW9uLmpzJztcbmltcG9ydCAnYm9vdHN0cmFwLXNhc3MvYXNzZXRzL2phdmFzY3JpcHRzL2Jvb3RzdHJhcC9jb2xsYXBzZS5qcyc7XG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vYXNzZXRzL2pzL2FwcC5qcyIsIi8qKlxuICogZmlsdGVyLmpzXG4gKlxuICogQGF1dGhvciBLb25yYWQgTWljaGFsaWsgPGtvbnJhZC5taWNoYWxpa0B4aW1hLmRlPlxuICogQHZlcnNpb24gMS4wLjBcbiAqIEBkZXBlbmRzXG4gKiAgICAgICAgalF1ZXJ5IDEuNC4xXG4gKlxuICovXG5GaWx0ZXIgPSAoZnVuY3Rpb24gKHdpbmRvdywgZG9jdW1lbnQsICQsIHVuZGVmaW5lZCkge1xuXG4gICAgdmFyIEVYVCA9IHt9O1xuICAgIHZhciBfc2VsZiA9IHRoaXM7XG5cblxuICAgIHZhciBfbmFtZXNwYWNlID0gJ2ZpbHRlci5qcyc7XG5cbiAgICB2YXIgX29wdGlvbnMgPSB7XG4gICAgICAgIC8vIGFycmF5IG9mIGFsbCBmaWx0ZXIgZm9ybXMsIHdoaWNoIHdpbGwgYmUgY29uc2lkZXJlZCBieSB0aGUgZmlsdGVyaW5nIHByb2Nlc3NcbiAgICAgICAgZmlsdGVyRm9ybXM6IFtdLFxuICAgICAgICAvLyBsaXN0IHRvIGJlIGZpbHRlcmVkXG4gICAgICAgIGxpc3Q6IG51bGwsXG4gICAgICAgIC8vIHVzZSBzZXNzaW9uIHN0b3JhZ2UgZm9yIGRhdGEgaGFuZGxpbmcgdG8gc3BlZWQgdXAgdGhlIGZpbHRlciBwcm9jZXNzXG4gICAgICAgIHN0b3JhZ2U6IGZhbHNlLFxuICAgICAgICAvLyBjc3MgY2xhc3MgdG8gaGlkZSB0aGUgdW5tYXRjaGVkIGxpc3QgZW50cmllc1xuICAgICAgICBoaWRlQ2xhc3M6ICdoaWRkZW4nLFxuICAgICAgICAvLyBlbGVtZW50cyB3aGljaCBzaG91bGQgYmUgZmlsdGVyZWRcbiAgICAgICAgZmlsdGVyYWJsZUVsZW1lbnRzOiAnbGknLFxuICAgICAgICAvLyBzdGFydCBmaWx0ZXJpbmcgb24gaW5wdXQgb3Igb24gZm9ybSBzdWJtaXRcbiAgICAgICAgZmlsdGVyT25TdWJtaXQ6IGZhbHNlLFxuICAgICAgICAvLyBjYWxsYmFjayBmdW5jdGlvbiBiZWZvcmUgbGlzdCBpcyBmaWx0ZXJlZFxuICAgICAgICBiZWZvcmVBcHBseUZpbHRlcjogZnVuY3Rpb24gKCkge1xuXG4gICAgICAgIH0sXG4gICAgICAgIC8vIGNhbGxiYWNrIGZ1bmN0aW9uIGFmdGVyIGxpc3QgaXMgZmlsdGVyZWRcbiAgICAgICAgYWZ0ZXJBcHBseUZpbHRlcjogZnVuY3Rpb24gKCkge1xuXG4gICAgICAgIH0sXG4gICAgICAgIC8vIGVuYWJsZSBkZWJ1ZyBvdXRwdXQgaW4gY29uc29sZVxuICAgICAgICBkZWJ1ZzogZmFsc2VcbiAgICB9O1xuXG4gICAgdmFyIF9lbGVtZW50cyA9IFtdO1xuXG4gICAgLypcbiAgICAgKiBJbml0aWFsaXplIGZpbHRlci5qc1xuICAgICAqXG4gICAgICogQHJldHVybiB0aGlzXG4gICAgICogQHBhcmFtIHBhcmFtZXRlcnNcbiAgICAgKi9cbiAgICBFWFQuaW5pdCA9IGZ1bmN0aW9uIChwYXJhbWV0ZXJzKSB7XG5cbiAgICAgICAgLy8gbG9hZCBhZGRpdGlvbmFsIHBhcmFtZXRlcnNcbiAgICAgICAgaWYgKHBhcmFtZXRlcnMpIHtcbiAgICAgICAgICAgIGpRdWVyeS5leHRlbmQoX29wdGlvbnMsIHBhcmFtZXRlcnMpO1xuICAgICAgICB9XG5cbiAgICAgICAgX29wdGlvbnMuZGVidWcgPyBjb25zb2xlLmxvZygnWycgKyBfbmFtZXNwYWNlICsgJ10gZmlsdGVyLmpzIGluaXRpYWxpemVkJykgOiAnJztcblxuICAgICAgICBfc2VsZi5pbml0RXZlbnRMaXN0ZW5lcigpO1xuXG4gICAgICAgIC8vIGNoZWNrIGZvciBzdG9yYWdlIG9wdGlvblxuICAgICAgICBpZiAoX29wdGlvbnMuc3RvcmFnZSkge1xuICAgICAgICAgICAgaWYgKHR5cGVvZihTdG9yYWdlKSAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgICAgICAgICBfc2VsZi5pbml0U3RvcmFnZSgpXG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHRocm93KCdbJyArIF9uYW1lc3BhY2UgKyAnXSBubyB3ZWJzdG9yYWdlIHN1cHBvcnQhJyk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogQ2xlYXIgc2Vzc2lvbiBzdG9yYWdlXG4gICAgICovXG4gICAgRVhULmNsZWFyU3RvcmFnZSA9IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgX2VsZW1lbnRzID0gW107XG4gICAgICAgIHNlc3Npb25TdG9yYWdlLmNsZWFyKCk7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIEluaXRpYWxpemUgZXZlbnQgbGlzdGVuZXJcbiAgICAgKi9cbiAgICB0aGlzLmluaXRFdmVudExpc3RlbmVyID0gZnVuY3Rpb24gKCkge1xuXG4gICAgICAgIF9vcHRpb25zLmRlYnVnID8gY29uc29sZS5sb2coJ1snICsgX25hbWVzcGFjZSArICddIGluaXQgZXZlbnQgbGlzdGVuZXInKSA6ICcnO1xuXG4gICAgICAgICQuZWFjaChfb3B0aW9ucy5maWx0ZXJGb3JtcywgZnVuY3Rpb24gKGluZGV4LCBmb3JtKSB7XG4gICAgICAgICAgICBpZiAoJChmb3JtKS5sZW5ndGgpIHtcbiAgICAgICAgICAgICAgICB2YXIgX2Zvcm0gPSAkKGZvcm0pO1xuICAgICAgICAgICAgICAgIF9mb3JtLnVuYmluZCgpO1xuXG4gICAgICAgICAgICAgICAgaWYgKF9vcHRpb25zLmZpbHRlck9uU3VibWl0KSB7XG4gICAgICAgICAgICAgICAgICAgIF9mb3JtLm9uKCdzdWJtaXQnLCBmdW5jdGlvbiAoZXZlbnQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICAgICAgICAgICAgICBfb3B0aW9ucy5kZWJ1ZyA/IGNvbnNvbGUubG9nKCdbJyArIF9uYW1lc3BhY2UgKyAnXSBmaWx0ZXIgc3VibWl0JykgOiAnJztcbiAgICAgICAgICAgICAgICAgICAgICAgIEVYVC5hcHBseUZpbHRlcigpO1xuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICBfZm9ybS5maW5kKCdpbnB1dCcpLm9uKCdpbnB1dCcsIGZ1bmN0aW9uIChldmVudCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgX29wdGlvbnMuZGVidWcgPyBjb25zb2xlLmxvZygnWycgKyBfbmFtZXNwYWNlICsgJ10gZmlsdGVyIGNoYW5nZScpIDogJyc7XG4gICAgICAgICAgICAgICAgICAgICAgICBFWFQuYXBwbHlGaWx0ZXIoKTtcbiAgICAgICAgICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgICAgICAgICAgICAgX2Zvcm0ub24oJ3N1Ym1pdCcsIGZ1bmN0aW9uIChldmVudCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogSW5pdGlhbGl6ZSBzZXNzaW9uIHN0b3JhZ2Ugd2hpbGUgcGFyc2luZyBsaXN0IGRhdGFcbiAgICAgKi9cbiAgICB0aGlzLmluaXRTdG9yYWdlID0gZnVuY3Rpb24gKCkge1xuXG4gICAgICAgIEVYVC5jbGVhclN0b3JhZ2UoKTtcbiAgICAgICAgdmFyIF9saXN0ID0gJChfb3B0aW9ucy5saXN0KTtcbiAgICAgICAgdmFyIF9lbnRyaWVzID0gX2xpc3QuZmluZChfb3B0aW9ucy5maWx0ZXJhYmxlRWxlbWVudHMpO1xuXG4gICAgICAgIF9vcHRpb25zLmRlYnVnID8gY29uc29sZS5sb2coJ1snICsgX25hbWVzcGFjZSArICddIGluaXQgc3RvcmFnZScpIDogJyc7XG5cbiAgICAgICAgJC5lYWNoKF9lbnRyaWVzLCBmdW5jdGlvbiAobnVtLCBlbnRyeSkge1xuICAgICAgICAgICAgdmFyIF9lbnRyeSA9ICQodGhpcyk7XG4gICAgICAgICAgICB2YXIgX2VsZW1lbnQgPSB7fTtcblxuICAgICAgICAgICAgJC5lYWNoKF9lbnRyeS5nZXQoMCkuYXR0cmlidXRlcywgZnVuY3Rpb24gKGksIGF0dHJpYikge1xuICAgICAgICAgICAgICAgIGlmIChhdHRyaWIubmFtZS5pbmRleE9mKCdkYXRhLScpICE9PSAtMSkge1xuICAgICAgICAgICAgICAgICAgICB2YXIgX25hbWUgPSBhdHRyaWIubmFtZS5yZXBsYWNlKCdkYXRhLScsICcnKTtcbiAgICAgICAgICAgICAgICAgICAgX2VsZW1lbnRbX25hbWVdID0gYXR0cmliLnZhbHVlO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICBfZWxlbWVudHMucHVzaChudW0pO1xuICAgICAgICAgICAgc2Vzc2lvblN0b3JhZ2Uuc2V0SXRlbShudW0sIEpTT04uc3RyaW5naWZ5KF9lbGVtZW50KSk7XG5cbiAgICAgICAgfSk7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIEJ1aWxkIHRoZSBmaWx0ZXIgb2JqZWN0XG4gICAgICovXG4gICAgdGhpcy5idWlsZEZpbHRlciA9IGZ1bmN0aW9uICgpIHtcblxuICAgICAgICB2YXIgX2ZpbHRlciA9IHt9O1xuXG4gICAgICAgIF9vcHRpb25zLmJlZm9yZUFwcGx5RmlsdGVyKCk7XG5cbiAgICAgICAgJC5lYWNoKF9vcHRpb25zLmZpbHRlckZvcm1zLCBmdW5jdGlvbiAoaW5kZXgsIGZvcm0pIHtcbiAgICAgICAgICAgIGlmICgkKGZvcm0pLmxlbmd0aCkge1xuICAgICAgICAgICAgICAgIHZhciBfZm9ybSA9ICQoZm9ybSk7XG4gICAgICAgICAgICAgICAgdmFyIF9mb3JtRGF0YSA9IF9mb3JtLnNlcmlhbGl6ZUFycmF5KCk7XG5cbiAgICAgICAgICAgICAgICAkLmVhY2goX2Zvcm1EYXRhLCBmdW5jdGlvbiAoa2V5LCB2YWx1ZSkge1xuICAgICAgICAgICAgICAgICAgICBpZiAodmFsdWVbJ3ZhbHVlJ10gIT09ICcnKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoX2ZpbHRlclt2YWx1ZVsnbmFtZSddXSAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdmFyIF9vYmplY3QgPSBfZmlsdGVyW3ZhbHVlWyduYW1lJ11dO1xuICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YXIgX29iamVjdCA9IHt9O1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgX29iamVjdFtPYmplY3Qua2V5cyhfb2JqZWN0KS5sZW5ndGhdID0gdmFsdWVbJ3ZhbHVlJ107XG4gICAgICAgICAgICAgICAgICAgICAgICBfZmlsdGVyW3ZhbHVlWyduYW1lJ11dID0gX29iamVjdDtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcblxuICAgICAgICByZXR1cm4gX2ZpbHRlcjtcblxuICAgIH07XG5cblxuICAgIC8qKlxuICAgICAqIEFwcGx5IHRoZSBmaWx0ZXIgc2V0dGluZ3MgdG8gdGhlIGdpdmVuIGxpc3RcbiAgICAgKi9cbiAgICBFWFQuYXBwbHlGaWx0ZXIgPSBmdW5jdGlvbiAoX2ZpbHRlciA9IG51bGwpIHtcblxuICAgICAgICBpZiAoX2ZpbHRlciA9PT0gbnVsbCkge1xuICAgICAgICAgICAgX2ZpbHRlciA9IF9zZWxmLmJ1aWxkRmlsdGVyKCk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBFWFQucmVzZXQoKTtcbiAgICAgICAgICAgIF9zZWxmLnN5bmNocm9uaXplRmlsdGVyRm9ybShfZmlsdGVyKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHZhciBfbGlzdCA9ICQoX29wdGlvbnMubGlzdCk7XG4gICAgICAgIHZhciBfZW50cmllcyA9IF9saXN0LmZpbmQoX29wdGlvbnMuZmlsdGVyYWJsZUVsZW1lbnRzKTtcblxuICAgICAgICBpZiAoX29wdGlvbnMuc3RvcmFnZSkge1xuICAgICAgICAgICAgJC5lYWNoKF9lbGVtZW50cywgZnVuY3Rpb24gKG51bSwgZW50cnkpIHtcbiAgICAgICAgICAgICAgICB2YXIgX3N0YXRlID0gW107XG4gICAgICAgICAgICAgICAgdmFyIF9lbGVtZW50ID0gSlNPTi5wYXJzZShzZXNzaW9uU3RvcmFnZS5nZXRJdGVtKGVudHJ5KSk7XG5cbiAgICAgICAgICAgICAgICAkLmVhY2goX2ZpbHRlciwgZnVuY3Rpb24gKGtleSwgdmFsdWUpIHtcbiAgICAgICAgICAgICAgICAgICAgaWYgKF9lbGVtZW50W2tleV0gIT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgLy8gVG9EbzogcmVzdHJpY3Qgb25seSBvbiBnaXZlbiBmb3Jtc1xuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCQoJ2lucHV0W25hbWU9XCInICsga2V5ICsgJ1wiXScpLmF0dHIoJ3R5cGUnKSA9PT0gJ3RleHQnKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgX3N0YXRlLnB1c2goX2VsZW1lbnRba2V5XS5pbmRleE9mKHZhbHVlWzBdKSA+PSAwKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgX3N0YXRlLnB1c2goalF1ZXJ5LmluQXJyYXkoX2VsZW1lbnRba2V5XSwgT2JqZWN0LnZhbHVlcyh2YWx1ZSkpICE9PSAtMSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9KTtcblxuICAgICAgICAgICAgICAgIGlmIChqUXVlcnkuaW5BcnJheShmYWxzZSwgX3N0YXRlKSAhPT0gLTEpIHtcbiAgICAgICAgICAgICAgICAgICAgJChfZW50cmllc1tudW1dKS5hZGRDbGFzcyhfb3B0aW9ucy5oaWRlQ2xhc3MpO1xuICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICQoX2VudHJpZXNbbnVtXSkucmVtb3ZlQ2xhc3MoX29wdGlvbnMuaGlkZUNsYXNzKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICQuZWFjaChfZW50cmllcywgZnVuY3Rpb24gKG51bSwgZW50cnkpIHtcbiAgICAgICAgICAgICAgICB2YXIgX3N0YXRlID0gW107XG4gICAgICAgICAgICAgICAgdmFyIF9lbnRyeSA9ICQodGhpcyk7XG5cbiAgICAgICAgICAgICAgICAkLmVhY2goX2ZpbHRlciwgZnVuY3Rpb24gKGtleSwgdmFsdWUpIHtcbiAgICAgICAgICAgICAgICAgICAgaWYgKF9lbnRyeS5hdHRyKCdkYXRhLScgKyBrZXkpKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAvLyBUb0RvOiByZXN0cmljdCBvbmx5IG9uIGdpdmVuIGZvcm1zXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoJCgnaW5wdXRbbmFtZT1cIicgKyBrZXkgKyAnXCJdJykuYXR0cigndHlwZScpID09PSAndGV4dCcpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBfc3RhdGUucHVzaChfZW50cnkuYXR0cignZGF0YS0nICsga2V5KS5pbmRleE9mKHZhbHVlWzBdKSA+PSAwKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgX3N0YXRlLnB1c2goalF1ZXJ5LmluQXJyYXkoX2VudHJ5LmF0dHIoJ2RhdGEtJyArIGtleSksIE9iamVjdC52YWx1ZXModmFsdWUpKSAhPT0gLTEpO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgICAgICAgICBpZiAoalF1ZXJ5LmluQXJyYXkoZmFsc2UsIF9zdGF0ZSkgIT09IC0xKSB7XG4gICAgICAgICAgICAgICAgICAgIF9lbnRyeS5hZGRDbGFzcyhfb3B0aW9ucy5oaWRlQ2xhc3MpO1xuICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIF9lbnRyeS5yZW1vdmVDbGFzcyhfb3B0aW9ucy5oaWRlQ2xhc3MpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG5cbiAgICAgICAgX29wdGlvbnMuYWZ0ZXJBcHBseUZpbHRlcigpO1xuXG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIEdldCBhIGdpdmVuIGZpbHRlciBhbmQgc3luY2hyb25pemUgdGhlIGZpbHRlciBmb3JtXG4gICAgICogQHBhcmFtIF9maWx0ZXJcbiAgICAgKi9cbiAgICB0aGlzLnN5bmNocm9uaXplRmlsdGVyRm9ybSA9IGZ1bmN0aW9uIChfZmlsdGVyKSB7XG5cbiAgICAgICAgJC5lYWNoKF9maWx0ZXIsIGZ1bmN0aW9uIChrZXksIHZhbHVlKSB7XG4gICAgICAgICAgICAvLyBUb0RvOiByZXN0cmljdCBvbmx5IG9uIGdpdmVuIGZvcm1zXG4gICAgICAgICAgICBpZiAoJCgnaW5wdXRbbmFtZT1cIicgKyBrZXkgKyAnXCJdJykuYXR0cigndHlwZScpID09PSAndGV4dCcpIHtcbiAgICAgICAgICAgICAgICAkKCdpbnB1dFtuYW1lPVwiJyArIGtleSArICdcIl0nKS52YWwodmFsdWUpO1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAkKCdpbnB1dFtuYW1lPVwiJyArIGtleSArICdcIl1bdmFsdWU9XCInICsgdmFsdWUgKyAnXCJdJykucHJvcCgnY2hlY2tlZCcsIHRydWUpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogUmVzZXQgdGhlIGZpbHRlciBmb3JtXG4gICAgICovXG4gICAgRVhULnJlc2V0ID0gZnVuY3Rpb24gKCkge1xuICAgICAgICAkLmVhY2goX29wdGlvbnMuZmlsdGVyRm9ybXMsIGZ1bmN0aW9uIChpbmRleCwgZm9ybSkge1xuICAgICAgICAgICAgaWYgKCQoZm9ybSkubGVuZ3RoKSB7XG4gICAgICAgICAgICAgICAgdmFyIF9mb3JtID0gJChmb3JtKTtcbiAgICAgICAgICAgICAgICBfZm9ybS51bmJpbmQoKTtcbiAgICAgICAgICAgICAgICBfZm9ybS5maW5kKCdpbnB1dCcpLmVhY2goZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgICAgICBpZiAoJCh0aGlzKS5hdHRyKCd0eXBlJykgPT09ICd0ZXh0Jykge1xuICAgICAgICAgICAgICAgICAgICAgICAgJCh0aGlzKS52YWwoJycpO1xuICAgICAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICAgICAgJCh0aGlzKS5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgIH07XG5cbiAgICByZXR1cm4gRVhUO1xuXG59KSh3aW5kb3csIGRvY3VtZW50LCBqUXVlcnkpO1xuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL2Fzc2V0cy9qcy9maWx0ZXIuanMiLCIkKGZ1bmN0aW9uKCkge1xuICAgIHZhciBmaWx0ZXIgPSBGaWx0ZXIuaW5pdCh7XG4gICAgICAgIGZpbHRlckZvcm1zOiBbXG4gICAgICAgICAgICAnI2RlcG1vbi1maWx0ZXInXG4gICAgICAgIF0sXG4gICAgICAgIGxpc3Q6ICcjZGVwbW9uLWxpc3QnLFxuICAgICAgICBzdG9yYWdlOiB0cnVlLFxuICAgICAgICBhZnRlckFwcGx5RmlsdGVyOiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICAvLyBoaWRlIGxpc3QgaGVhZGVyIHdpdGggbm8gbW9yZSBlbnRyaWVzIGluIHRoZSBsaXN0XG4gICAgICAgICAgICAkKCcubGlzdC1oZWFkZXInKS5yZW1vdmVDbGFzcygnaGlkZGVuJyk7XG4gICAgICAgICAgICBpZiAoISQoJyNkZXBtb24tZmlsdGVyIGlucHV0W25hbWU9XCJvdmVydmlld1wiXTpjaGVja2VkJykubGVuZ3RoKSB7XG4gICAgICAgICAgICAgICAgJCgnI2RlcG1vbi1saXN0IHVsOm5vdCguaGVhZGVyKScpLmVhY2goZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgICAgICB2YXIgbGlzdCA9ICQodGhpcyk7XG4gICAgICAgICAgICAgICAgICAgIGlmIChsaXN0LmZpbmQoJ2xpOm5vdCguaGlkZGVuKTpub3QoLmxpc3QtaGVhZGVyKScpLmxlbmd0aCA9PT0gMCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIGxpc3RIZWFkZXIgPSAkKGxpc3QuZmluZCgnbGkubGlzdC1oZWFkZXInKSlbMF07XG4gICAgICAgICAgICAgICAgICAgICAgICAkKGxpc3RIZWFkZXIpLmFkZENsYXNzKCdoaWRkZW4nKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9LFxuICAgICAgICBkZWJ1ZzogdHJ1ZVxuICAgIH0pO1xuXG4gICAgJCgnLmZpbHRlci1yZXNldCcpLmNsaWNrKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgZmlsdGVyLnJlc2V0KCk7XG4gICAgICAgIGZpbHRlci5hcHBseUZpbHRlcihudWxsKTtcbiAgICAgICAgJCgnLmNvbGxhcHNlJykuY29sbGFwc2UoJ2hpZGUnKTtcbiAgICB9KTtcblxuICAgIC8vIFRvRG86IENvbWJpbmUgdGhlc2UgZXZlbnQgbGlzdGVuZXIgdG8gYSBtb3JlIGdlbmVyaWMgb25lXG4gICAgLy8gUXVpY2sgZmlsdGVyIGZvciBwcm9qZWN0XG4gICAgJCgnLmZpbHRlci1wcm9qZWN0JykuY2xpY2soZnVuY3Rpb24oKSB7XG4gICAgICAgIHZhciBwcm9qZWN0ID0gJCh0aGlzKS5hdHRyKCdkYXRhLXByb2plY3QnKTtcbiAgICAgICAgdmFyIG9iamVjdCA9IHtcbiAgICAgICAgICAgICdwcm9qZWN0JzogW1xuICAgICAgICAgICAgICAgIHByb2plY3RcbiAgICAgICAgICAgIF1cbiAgICAgICAgfTtcbiAgICAgICAgZmlsdGVyLmFwcGx5RmlsdGVyKG9iamVjdCk7XG4gICAgICAgICQoJyNmaWx0ZXItcHJvamVjdC1jb2xsYXBzZScpLmNvbGxhcHNlKCdzaG93Jyk7XG4gICAgfSk7XG5cbiAgICAvLyBRdWljayBmaWx0ZXIgZm9yIGRlcGVuZGVuY3lcbiAgICAkKCcuZmlsdGVyLWRlcGVuZGVuY3knKS5jbGljayhmdW5jdGlvbigpIHtcbiAgICAgICAgdmFyIGRlcGVuZGVuY3kgPSAkKHRoaXMpLmF0dHIoJ2RhdGEtZGVwZW5kZW5jeScpO1xuICAgICAgICB2YXIgb2JqZWN0ID0ge1xuICAgICAgICAgICAgJ25hbWUnOiBbXG4gICAgICAgICAgICAgICAgZGVwZW5kZW5jeVxuICAgICAgICAgICAgXVxuICAgICAgICB9O1xuICAgICAgICBmaWx0ZXIuYXBwbHlGaWx0ZXIob2JqZWN0KTtcbiAgICB9KTtcblxuICAgIC8vIFF1aWNrIGZpbHRlciBmb3IgdHlwZVxuICAgICQoJy5maWx0ZXItdHlwZScpLmNsaWNrKGZ1bmN0aW9uKCkge1xuICAgICAgICB2YXIgdHlwZSA9ICQodGhpcykuYXR0cignZGF0YS10eXBlJyk7XG4gICAgICAgIHZhciBvYmplY3QgPSB7XG4gICAgICAgICAgICAncHJvamVjdC10eXBlJzogW1xuICAgICAgICAgICAgICAgIHR5cGVcbiAgICAgICAgICAgIF1cbiAgICAgICAgfTtcbiAgICAgICAgZmlsdGVyLmFwcGx5RmlsdGVyKG9iamVjdCk7XG4gICAgICAgICQoJyNmaWx0ZXItdHlwZS1jb2xsYXBzZScpLmNvbGxhcHNlKCdzaG93Jyk7XG4gICAgfSk7XG5cbiAgICAvLyBRdWljayBmaWx0ZXIgZm9yIHN0YXRlIGFuZCB0aGUgcHJvamVjdFxuICAgICQoJy5maWx0ZXItc3RhdGUtcHJvamVjdCcpLmNsaWNrKGZ1bmN0aW9uKCkge1xuICAgICAgICB2YXIgcHJvamVjdCA9ICQodGhpcykuYXR0cignZGF0YS1wcm9qZWN0Jyk7XG4gICAgICAgIHZhciBzdGF0ZSA9ICQodGhpcykuYXR0cignZGF0YS1zdGF0ZScpO1xuICAgICAgICB2YXIgb2JqZWN0ID0ge1xuICAgICAgICAgICAgJ3Byb2plY3QnOiBbXG4gICAgICAgICAgICAgICAgcHJvamVjdFxuICAgICAgICAgICAgXSxcbiAgICAgICAgICAgICdzdGF0ZSc6IFtcbiAgICAgICAgICAgICAgICBzdGF0ZVxuICAgICAgICAgICAgXVxuICAgICAgICB9O1xuICAgICAgICBmaWx0ZXIuYXBwbHlGaWx0ZXIob2JqZWN0KTtcbiAgICAgICAgJCgnI2ZpbHRlci1wcm9qZWN0LWNvbGxhcHNlJykuY29sbGFwc2UoJ3Nob3cnKTtcbiAgICAgICAgJCgnI2ZpbHRlci1zdGF0ZS1jb2xsYXBzZScpLmNvbGxhcHNlKCdzaG93Jyk7XG4gICAgfSk7XG5cbiAgICAvLyBRdWljayBmaWx0ZXIgZm9yIHR5cGVcbiAgICAkKCcuZmlsdGVyLXJlcXVpcmVkLXByb2plY3QnKS5jbGljayhmdW5jdGlvbigpIHtcbiAgICAgICAgdmFyIHByb2plY3QgPSAkKHRoaXMpLmF0dHIoJ2RhdGEtcHJvamVjdCcpO1xuICAgICAgICB2YXIgcmVxdWlyZWQgPSAkKHRoaXMpLmF0dHIoJ2RhdGEtcmVxdWlyZWQnKTtcbiAgICAgICAgdmFyIG9iamVjdCA9IHtcbiAgICAgICAgICAgICdwcm9qZWN0JzogW1xuICAgICAgICAgICAgICAgIHByb2plY3RcbiAgICAgICAgICAgIF0sXG4gICAgICAgICAgICAncmVxdWlyZWQnOiBbXG4gICAgICAgICAgICAgICAgcmVxdWlyZWRcbiAgICAgICAgICAgIF1cbiAgICAgICAgfTtcbiAgICAgICAgZmlsdGVyLmFwcGx5RmlsdGVyKG9iamVjdCk7XG4gICAgICAgICQoJyNmaWx0ZXItcHJvamVjdC1jb2xsYXBzZScpLmNvbGxhcHNlKCdzaG93Jyk7XG4gICAgfSk7XG59KTtcblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gLi9hc3NldHMvanMvc2VhcmNoLmpzIiwiLyogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG4gKiBCb290c3RyYXA6IGNvbGxhcHNlLmpzIHYzLjMuN1xuICogaHR0cDovL2dldGJvb3RzdHJhcC5jb20vamF2YXNjcmlwdC8jY29sbGFwc2VcbiAqID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuICogQ29weXJpZ2h0IDIwMTEtMjAxNiBUd2l0dGVyLCBJbmMuXG4gKiBMaWNlbnNlZCB1bmRlciBNSVQgKGh0dHBzOi8vZ2l0aHViLmNvbS90d2JzL2Jvb3RzdHJhcC9ibG9iL21hc3Rlci9MSUNFTlNFKVxuICogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovXG5cbi8qIGpzaGludCBsYXRlZGVmOiBmYWxzZSAqL1xuXG4rZnVuY3Rpb24gKCQpIHtcbiAgJ3VzZSBzdHJpY3QnO1xuXG4gIC8vIENPTExBUFNFIFBVQkxJQyBDTEFTUyBERUZJTklUSU9OXG4gIC8vID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cbiAgdmFyIENvbGxhcHNlID0gZnVuY3Rpb24gKGVsZW1lbnQsIG9wdGlvbnMpIHtcbiAgICB0aGlzLiRlbGVtZW50ICAgICAgPSAkKGVsZW1lbnQpXG4gICAgdGhpcy5vcHRpb25zICAgICAgID0gJC5leHRlbmQoe30sIENvbGxhcHNlLkRFRkFVTFRTLCBvcHRpb25zKVxuICAgIHRoaXMuJHRyaWdnZXIgICAgICA9ICQoJ1tkYXRhLXRvZ2dsZT1cImNvbGxhcHNlXCJdW2hyZWY9XCIjJyArIGVsZW1lbnQuaWQgKyAnXCJdLCcgK1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgJ1tkYXRhLXRvZ2dsZT1cImNvbGxhcHNlXCJdW2RhdGEtdGFyZ2V0PVwiIycgKyBlbGVtZW50LmlkICsgJ1wiXScpXG4gICAgdGhpcy50cmFuc2l0aW9uaW5nID0gbnVsbFxuXG4gICAgaWYgKHRoaXMub3B0aW9ucy5wYXJlbnQpIHtcbiAgICAgIHRoaXMuJHBhcmVudCA9IHRoaXMuZ2V0UGFyZW50KClcbiAgICB9IGVsc2Uge1xuICAgICAgdGhpcy5hZGRBcmlhQW5kQ29sbGFwc2VkQ2xhc3ModGhpcy4kZWxlbWVudCwgdGhpcy4kdHJpZ2dlcilcbiAgICB9XG5cbiAgICBpZiAodGhpcy5vcHRpb25zLnRvZ2dsZSkgdGhpcy50b2dnbGUoKVxuICB9XG5cbiAgQ29sbGFwc2UuVkVSU0lPTiAgPSAnMy4zLjcnXG5cbiAgQ29sbGFwc2UuVFJBTlNJVElPTl9EVVJBVElPTiA9IDM1MFxuXG4gIENvbGxhcHNlLkRFRkFVTFRTID0ge1xuICAgIHRvZ2dsZTogdHJ1ZVxuICB9XG5cbiAgQ29sbGFwc2UucHJvdG90eXBlLmRpbWVuc2lvbiA9IGZ1bmN0aW9uICgpIHtcbiAgICB2YXIgaGFzV2lkdGggPSB0aGlzLiRlbGVtZW50Lmhhc0NsYXNzKCd3aWR0aCcpXG4gICAgcmV0dXJuIGhhc1dpZHRoID8gJ3dpZHRoJyA6ICdoZWlnaHQnXG4gIH1cblxuICBDb2xsYXBzZS5wcm90b3R5cGUuc2hvdyA9IGZ1bmN0aW9uICgpIHtcbiAgICBpZiAodGhpcy50cmFuc2l0aW9uaW5nIHx8IHRoaXMuJGVsZW1lbnQuaGFzQ2xhc3MoJ2luJykpIHJldHVyblxuXG4gICAgdmFyIGFjdGl2ZXNEYXRhXG4gICAgdmFyIGFjdGl2ZXMgPSB0aGlzLiRwYXJlbnQgJiYgdGhpcy4kcGFyZW50LmNoaWxkcmVuKCcucGFuZWwnKS5jaGlsZHJlbignLmluLCAuY29sbGFwc2luZycpXG5cbiAgICBpZiAoYWN0aXZlcyAmJiBhY3RpdmVzLmxlbmd0aCkge1xuICAgICAgYWN0aXZlc0RhdGEgPSBhY3RpdmVzLmRhdGEoJ2JzLmNvbGxhcHNlJylcbiAgICAgIGlmIChhY3RpdmVzRGF0YSAmJiBhY3RpdmVzRGF0YS50cmFuc2l0aW9uaW5nKSByZXR1cm5cbiAgICB9XG5cbiAgICB2YXIgc3RhcnRFdmVudCA9ICQuRXZlbnQoJ3Nob3cuYnMuY29sbGFwc2UnKVxuICAgIHRoaXMuJGVsZW1lbnQudHJpZ2dlcihzdGFydEV2ZW50KVxuICAgIGlmIChzdGFydEV2ZW50LmlzRGVmYXVsdFByZXZlbnRlZCgpKSByZXR1cm5cblxuICAgIGlmIChhY3RpdmVzICYmIGFjdGl2ZXMubGVuZ3RoKSB7XG4gICAgICBQbHVnaW4uY2FsbChhY3RpdmVzLCAnaGlkZScpXG4gICAgICBhY3RpdmVzRGF0YSB8fCBhY3RpdmVzLmRhdGEoJ2JzLmNvbGxhcHNlJywgbnVsbClcbiAgICB9XG5cbiAgICB2YXIgZGltZW5zaW9uID0gdGhpcy5kaW1lbnNpb24oKVxuXG4gICAgdGhpcy4kZWxlbWVudFxuICAgICAgLnJlbW92ZUNsYXNzKCdjb2xsYXBzZScpXG4gICAgICAuYWRkQ2xhc3MoJ2NvbGxhcHNpbmcnKVtkaW1lbnNpb25dKDApXG4gICAgICAuYXR0cignYXJpYS1leHBhbmRlZCcsIHRydWUpXG5cbiAgICB0aGlzLiR0cmlnZ2VyXG4gICAgICAucmVtb3ZlQ2xhc3MoJ2NvbGxhcHNlZCcpXG4gICAgICAuYXR0cignYXJpYS1leHBhbmRlZCcsIHRydWUpXG5cbiAgICB0aGlzLnRyYW5zaXRpb25pbmcgPSAxXG5cbiAgICB2YXIgY29tcGxldGUgPSBmdW5jdGlvbiAoKSB7XG4gICAgICB0aGlzLiRlbGVtZW50XG4gICAgICAgIC5yZW1vdmVDbGFzcygnY29sbGFwc2luZycpXG4gICAgICAgIC5hZGRDbGFzcygnY29sbGFwc2UgaW4nKVtkaW1lbnNpb25dKCcnKVxuICAgICAgdGhpcy50cmFuc2l0aW9uaW5nID0gMFxuICAgICAgdGhpcy4kZWxlbWVudFxuICAgICAgICAudHJpZ2dlcignc2hvd24uYnMuY29sbGFwc2UnKVxuICAgIH1cblxuICAgIGlmICghJC5zdXBwb3J0LnRyYW5zaXRpb24pIHJldHVybiBjb21wbGV0ZS5jYWxsKHRoaXMpXG5cbiAgICB2YXIgc2Nyb2xsU2l6ZSA9ICQuY2FtZWxDYXNlKFsnc2Nyb2xsJywgZGltZW5zaW9uXS5qb2luKCctJykpXG5cbiAgICB0aGlzLiRlbGVtZW50XG4gICAgICAub25lKCdic1RyYW5zaXRpb25FbmQnLCAkLnByb3h5KGNvbXBsZXRlLCB0aGlzKSlcbiAgICAgIC5lbXVsYXRlVHJhbnNpdGlvbkVuZChDb2xsYXBzZS5UUkFOU0lUSU9OX0RVUkFUSU9OKVtkaW1lbnNpb25dKHRoaXMuJGVsZW1lbnRbMF1bc2Nyb2xsU2l6ZV0pXG4gIH1cblxuICBDb2xsYXBzZS5wcm90b3R5cGUuaGlkZSA9IGZ1bmN0aW9uICgpIHtcbiAgICBpZiAodGhpcy50cmFuc2l0aW9uaW5nIHx8ICF0aGlzLiRlbGVtZW50Lmhhc0NsYXNzKCdpbicpKSByZXR1cm5cblxuICAgIHZhciBzdGFydEV2ZW50ID0gJC5FdmVudCgnaGlkZS5icy5jb2xsYXBzZScpXG4gICAgdGhpcy4kZWxlbWVudC50cmlnZ2VyKHN0YXJ0RXZlbnQpXG4gICAgaWYgKHN0YXJ0RXZlbnQuaXNEZWZhdWx0UHJldmVudGVkKCkpIHJldHVyblxuXG4gICAgdmFyIGRpbWVuc2lvbiA9IHRoaXMuZGltZW5zaW9uKClcblxuICAgIHRoaXMuJGVsZW1lbnRbZGltZW5zaW9uXSh0aGlzLiRlbGVtZW50W2RpbWVuc2lvbl0oKSlbMF0ub2Zmc2V0SGVpZ2h0XG5cbiAgICB0aGlzLiRlbGVtZW50XG4gICAgICAuYWRkQ2xhc3MoJ2NvbGxhcHNpbmcnKVxuICAgICAgLnJlbW92ZUNsYXNzKCdjb2xsYXBzZSBpbicpXG4gICAgICAuYXR0cignYXJpYS1leHBhbmRlZCcsIGZhbHNlKVxuXG4gICAgdGhpcy4kdHJpZ2dlclxuICAgICAgLmFkZENsYXNzKCdjb2xsYXBzZWQnKVxuICAgICAgLmF0dHIoJ2FyaWEtZXhwYW5kZWQnLCBmYWxzZSlcblxuICAgIHRoaXMudHJhbnNpdGlvbmluZyA9IDFcblxuICAgIHZhciBjb21wbGV0ZSA9IGZ1bmN0aW9uICgpIHtcbiAgICAgIHRoaXMudHJhbnNpdGlvbmluZyA9IDBcbiAgICAgIHRoaXMuJGVsZW1lbnRcbiAgICAgICAgLnJlbW92ZUNsYXNzKCdjb2xsYXBzaW5nJylcbiAgICAgICAgLmFkZENsYXNzKCdjb2xsYXBzZScpXG4gICAgICAgIC50cmlnZ2VyKCdoaWRkZW4uYnMuY29sbGFwc2UnKVxuICAgIH1cblxuICAgIGlmICghJC5zdXBwb3J0LnRyYW5zaXRpb24pIHJldHVybiBjb21wbGV0ZS5jYWxsKHRoaXMpXG5cbiAgICB0aGlzLiRlbGVtZW50XG4gICAgICBbZGltZW5zaW9uXSgwKVxuICAgICAgLm9uZSgnYnNUcmFuc2l0aW9uRW5kJywgJC5wcm94eShjb21wbGV0ZSwgdGhpcykpXG4gICAgICAuZW11bGF0ZVRyYW5zaXRpb25FbmQoQ29sbGFwc2UuVFJBTlNJVElPTl9EVVJBVElPTilcbiAgfVxuXG4gIENvbGxhcHNlLnByb3RvdHlwZS50b2dnbGUgPSBmdW5jdGlvbiAoKSB7XG4gICAgdGhpc1t0aGlzLiRlbGVtZW50Lmhhc0NsYXNzKCdpbicpID8gJ2hpZGUnIDogJ3Nob3cnXSgpXG4gIH1cblxuICBDb2xsYXBzZS5wcm90b3R5cGUuZ2V0UGFyZW50ID0gZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiAkKHRoaXMub3B0aW9ucy5wYXJlbnQpXG4gICAgICAuZmluZCgnW2RhdGEtdG9nZ2xlPVwiY29sbGFwc2VcIl1bZGF0YS1wYXJlbnQ9XCInICsgdGhpcy5vcHRpb25zLnBhcmVudCArICdcIl0nKVxuICAgICAgLmVhY2goJC5wcm94eShmdW5jdGlvbiAoaSwgZWxlbWVudCkge1xuICAgICAgICB2YXIgJGVsZW1lbnQgPSAkKGVsZW1lbnQpXG4gICAgICAgIHRoaXMuYWRkQXJpYUFuZENvbGxhcHNlZENsYXNzKGdldFRhcmdldEZyb21UcmlnZ2VyKCRlbGVtZW50KSwgJGVsZW1lbnQpXG4gICAgICB9LCB0aGlzKSlcbiAgICAgIC5lbmQoKVxuICB9XG5cbiAgQ29sbGFwc2UucHJvdG90eXBlLmFkZEFyaWFBbmRDb2xsYXBzZWRDbGFzcyA9IGZ1bmN0aW9uICgkZWxlbWVudCwgJHRyaWdnZXIpIHtcbiAgICB2YXIgaXNPcGVuID0gJGVsZW1lbnQuaGFzQ2xhc3MoJ2luJylcblxuICAgICRlbGVtZW50LmF0dHIoJ2FyaWEtZXhwYW5kZWQnLCBpc09wZW4pXG4gICAgJHRyaWdnZXJcbiAgICAgIC50b2dnbGVDbGFzcygnY29sbGFwc2VkJywgIWlzT3BlbilcbiAgICAgIC5hdHRyKCdhcmlhLWV4cGFuZGVkJywgaXNPcGVuKVxuICB9XG5cbiAgZnVuY3Rpb24gZ2V0VGFyZ2V0RnJvbVRyaWdnZXIoJHRyaWdnZXIpIHtcbiAgICB2YXIgaHJlZlxuICAgIHZhciB0YXJnZXQgPSAkdHJpZ2dlci5hdHRyKCdkYXRhLXRhcmdldCcpXG4gICAgICB8fCAoaHJlZiA9ICR0cmlnZ2VyLmF0dHIoJ2hyZWYnKSkgJiYgaHJlZi5yZXBsYWNlKC8uKig/PSNbXlxcc10rJCkvLCAnJykgLy8gc3RyaXAgZm9yIGllN1xuXG4gICAgcmV0dXJuICQodGFyZ2V0KVxuICB9XG5cblxuICAvLyBDT0xMQVBTRSBQTFVHSU4gREVGSU5JVElPTlxuICAvLyA9PT09PT09PT09PT09PT09PT09PT09PT09PVxuXG4gIGZ1bmN0aW9uIFBsdWdpbihvcHRpb24pIHtcbiAgICByZXR1cm4gdGhpcy5lYWNoKGZ1bmN0aW9uICgpIHtcbiAgICAgIHZhciAkdGhpcyAgID0gJCh0aGlzKVxuICAgICAgdmFyIGRhdGEgICAgPSAkdGhpcy5kYXRhKCdicy5jb2xsYXBzZScpXG4gICAgICB2YXIgb3B0aW9ucyA9ICQuZXh0ZW5kKHt9LCBDb2xsYXBzZS5ERUZBVUxUUywgJHRoaXMuZGF0YSgpLCB0eXBlb2Ygb3B0aW9uID09ICdvYmplY3QnICYmIG9wdGlvbilcblxuICAgICAgaWYgKCFkYXRhICYmIG9wdGlvbnMudG9nZ2xlICYmIC9zaG93fGhpZGUvLnRlc3Qob3B0aW9uKSkgb3B0aW9ucy50b2dnbGUgPSBmYWxzZVxuICAgICAgaWYgKCFkYXRhKSAkdGhpcy5kYXRhKCdicy5jb2xsYXBzZScsIChkYXRhID0gbmV3IENvbGxhcHNlKHRoaXMsIG9wdGlvbnMpKSlcbiAgICAgIGlmICh0eXBlb2Ygb3B0aW9uID09ICdzdHJpbmcnKSBkYXRhW29wdGlvbl0oKVxuICAgIH0pXG4gIH1cblxuICB2YXIgb2xkID0gJC5mbi5jb2xsYXBzZVxuXG4gICQuZm4uY29sbGFwc2UgICAgICAgICAgICAgPSBQbHVnaW5cbiAgJC5mbi5jb2xsYXBzZS5Db25zdHJ1Y3RvciA9IENvbGxhcHNlXG5cblxuICAvLyBDT0xMQVBTRSBOTyBDT05GTElDVFxuICAvLyA9PT09PT09PT09PT09PT09PT09PVxuXG4gICQuZm4uY29sbGFwc2Uubm9Db25mbGljdCA9IGZ1bmN0aW9uICgpIHtcbiAgICAkLmZuLmNvbGxhcHNlID0gb2xkXG4gICAgcmV0dXJuIHRoaXNcbiAgfVxuXG5cbiAgLy8gQ09MTEFQU0UgREFUQS1BUElcbiAgLy8gPT09PT09PT09PT09PT09PT1cblxuICAkKGRvY3VtZW50KS5vbignY2xpY2suYnMuY29sbGFwc2UuZGF0YS1hcGknLCAnW2RhdGEtdG9nZ2xlPVwiY29sbGFwc2VcIl0nLCBmdW5jdGlvbiAoZSkge1xuICAgIHZhciAkdGhpcyAgID0gJCh0aGlzKVxuXG4gICAgaWYgKCEkdGhpcy5hdHRyKCdkYXRhLXRhcmdldCcpKSBlLnByZXZlbnREZWZhdWx0KClcblxuICAgIHZhciAkdGFyZ2V0ID0gZ2V0VGFyZ2V0RnJvbVRyaWdnZXIoJHRoaXMpXG4gICAgdmFyIGRhdGEgICAgPSAkdGFyZ2V0LmRhdGEoJ2JzLmNvbGxhcHNlJylcbiAgICB2YXIgb3B0aW9uICA9IGRhdGEgPyAndG9nZ2xlJyA6ICR0aGlzLmRhdGEoKVxuXG4gICAgUGx1Z2luLmNhbGwoJHRhcmdldCwgb3B0aW9uKVxuICB9KVxuXG59KGpRdWVyeSk7XG5cblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL34vYm9vdHN0cmFwLXNhc3MvYXNzZXRzL2phdmFzY3JpcHRzL2Jvb3RzdHJhcC9jb2xsYXBzZS5qc1xuLy8gbW9kdWxlIGlkID0gLi9ub2RlX21vZHVsZXMvYm9vdHN0cmFwLXNhc3MvYXNzZXRzL2phdmFzY3JpcHRzL2Jvb3RzdHJhcC9jb2xsYXBzZS5qc1xuLy8gbW9kdWxlIGNodW5rcyA9IDEiLCIvKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cbiAqIEJvb3RzdHJhcDogdHJhbnNpdGlvbi5qcyB2My4zLjdcbiAqIGh0dHA6Ly9nZXRib290c3RyYXAuY29tL2phdmFzY3JpcHQvI3RyYW5zaXRpb25zXG4gKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cbiAqIENvcHlyaWdodCAyMDExLTIwMTYgVHdpdHRlciwgSW5jLlxuICogTGljZW5zZWQgdW5kZXIgTUlUIChodHRwczovL2dpdGh1Yi5jb20vdHdicy9ib290c3RyYXAvYmxvYi9tYXN0ZXIvTElDRU5TRSlcbiAqID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PSAqL1xuXG5cbitmdW5jdGlvbiAoJCkge1xuICAndXNlIHN0cmljdCc7XG5cbiAgLy8gQ1NTIFRSQU5TSVRJT04gU1VQUE9SVCAoU2hvdXRvdXQ6IGh0dHA6Ly93d3cubW9kZXJuaXpyLmNvbS8pXG4gIC8vID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuXG4gIGZ1bmN0aW9uIHRyYW5zaXRpb25FbmQoKSB7XG4gICAgdmFyIGVsID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnYm9vdHN0cmFwJylcblxuICAgIHZhciB0cmFuc0VuZEV2ZW50TmFtZXMgPSB7XG4gICAgICBXZWJraXRUcmFuc2l0aW9uIDogJ3dlYmtpdFRyYW5zaXRpb25FbmQnLFxuICAgICAgTW96VHJhbnNpdGlvbiAgICA6ICd0cmFuc2l0aW9uZW5kJyxcbiAgICAgIE9UcmFuc2l0aW9uICAgICAgOiAnb1RyYW5zaXRpb25FbmQgb3RyYW5zaXRpb25lbmQnLFxuICAgICAgdHJhbnNpdGlvbiAgICAgICA6ICd0cmFuc2l0aW9uZW5kJ1xuICAgIH1cblxuICAgIGZvciAodmFyIG5hbWUgaW4gdHJhbnNFbmRFdmVudE5hbWVzKSB7XG4gICAgICBpZiAoZWwuc3R5bGVbbmFtZV0gIT09IHVuZGVmaW5lZCkge1xuICAgICAgICByZXR1cm4geyBlbmQ6IHRyYW5zRW5kRXZlbnROYW1lc1tuYW1lXSB9XG4gICAgICB9XG4gICAgfVxuXG4gICAgcmV0dXJuIGZhbHNlIC8vIGV4cGxpY2l0IGZvciBpZTggKCAgLl8uKVxuICB9XG5cbiAgLy8gaHR0cDovL2Jsb2cuYWxleG1hY2Nhdy5jb20vY3NzLXRyYW5zaXRpb25zXG4gICQuZm4uZW11bGF0ZVRyYW5zaXRpb25FbmQgPSBmdW5jdGlvbiAoZHVyYXRpb24pIHtcbiAgICB2YXIgY2FsbGVkID0gZmFsc2VcbiAgICB2YXIgJGVsID0gdGhpc1xuICAgICQodGhpcykub25lKCdic1RyYW5zaXRpb25FbmQnLCBmdW5jdGlvbiAoKSB7IGNhbGxlZCA9IHRydWUgfSlcbiAgICB2YXIgY2FsbGJhY2sgPSBmdW5jdGlvbiAoKSB7IGlmICghY2FsbGVkKSAkKCRlbCkudHJpZ2dlcigkLnN1cHBvcnQudHJhbnNpdGlvbi5lbmQpIH1cbiAgICBzZXRUaW1lb3V0KGNhbGxiYWNrLCBkdXJhdGlvbilcbiAgICByZXR1cm4gdGhpc1xuICB9XG5cbiAgJChmdW5jdGlvbiAoKSB7XG4gICAgJC5zdXBwb3J0LnRyYW5zaXRpb24gPSB0cmFuc2l0aW9uRW5kKClcblxuICAgIGlmICghJC5zdXBwb3J0LnRyYW5zaXRpb24pIHJldHVyblxuXG4gICAgJC5ldmVudC5zcGVjaWFsLmJzVHJhbnNpdGlvbkVuZCA9IHtcbiAgICAgIGJpbmRUeXBlOiAkLnN1cHBvcnQudHJhbnNpdGlvbi5lbmQsXG4gICAgICBkZWxlZ2F0ZVR5cGU6ICQuc3VwcG9ydC50cmFuc2l0aW9uLmVuZCxcbiAgICAgIGhhbmRsZTogZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgaWYgKCQoZS50YXJnZXQpLmlzKHRoaXMpKSByZXR1cm4gZS5oYW5kbGVPYmouaGFuZGxlci5hcHBseSh0aGlzLCBhcmd1bWVudHMpXG4gICAgICB9XG4gICAgfVxuICB9KVxuXG59KGpRdWVyeSk7XG5cblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL34vYm9vdHN0cmFwLXNhc3MvYXNzZXRzL2phdmFzY3JpcHRzL2Jvb3RzdHJhcC90cmFuc2l0aW9uLmpzXG4vLyBtb2R1bGUgaWQgPSAuL25vZGVfbW9kdWxlcy9ib290c3RyYXAtc2Fzcy9hc3NldHMvamF2YXNjcmlwdHMvYm9vdHN0cmFwL3RyYW5zaXRpb24uanNcbi8vIG1vZHVsZSBjaHVua3MgPSAxIl0sInNvdXJjZVJvb3QiOiIifQ==