;(function($, window, StateManager, undefined) {
    'use strict';

    var $body = $('body');

    /**
     * Plugin for handling the filter functionality and
     * all other actions for changing the product listing.
     * It handles the current set of category parameters and applies
     * them to the current top location url when something was
     * changed by the user over the filter form, action forms or
     * the action links.
     *
     * ** Filter Form **
     * The filter form exists of different filter components,
     * the filter submit button and the labels for active filters.
     * Each component is rendered in a single panel and has its own functionality.
     * All single components are handled by the "filterComponent" plugin.
     * The plugin for the components fires correct change events for each type
     * of component, so the "listingActions" plugin can listen on the changes
     * of the user. A filter form has to be a normal form with the selector,
     * which is set in the plugin options, so the form can be found by the plugin.
     * The actual submitting of the form will always be prevented to build the complex
     * category parameters out of the serialized form data.
     *
     * Example:
     * <form id="filter" method="get" data-filter-form="true">
     *
     *
     * ** Action Forms **
     * You can apply different category parameters over additional action forms.
     * In most cases these forms are auto submitting forms using the "autoSubmit" plugin,
     * which change just one parameter via a combo- or checkbox. So with these
     * action forms you have the possibility to apply all kind of category parameters
     * like sorting, layout type, number of products per page etc.
     *
     * Example:
     * <form method="get" data-action-form="true">
     *  <select name="{$shortParameters.sSort}" data-auto-submit="true">
     *      {...}
     *  </select>
     * </form>
     *
     *
     * ** Action Links **
     * You can also apply different category parameter via direct links.
     * Just use the corresponding get parameters in the href attribute of the link.
     * The new parameter will be added to the existing category parameters.
     * If the parameter already exists the value will be updated with the new one.
     *
     * Example:
     * <a href="?p=1&l=list" data-action-link="true">list view</a>
     *
     */
    $.plugin('swListingActions', {

        defaults: {

            /**
             * The selector for the filter panel form.
             */
            filterFormSelector: '*[data-filter-form="true"]',

            /**
             * The selector for the single filter components.
             */
            filterComponentSelector: '*[data-filter-type]',

            /**
             * The selector for the button which shows and hides the filter panel.
             */
            filterTriggerSelector: '*[data-filter-trigger="true"]',

            /**
             * The selector for the icon inside the filter trigger button.
             */
            filterTriggerIconSelector: '.action--collapse-icon',

            /**
             * The selector for the filter panel element.
             */
            filterContainerSelector: '.action--filter-options',

            /**
             * The selector for additional listing action forms.
             */
            actionFormSelector: '*[data-action-form="true"]',

            /**
             * The selector for additional listing action links.
             */
            actionLinkSelector: '*[data-action-link="true"]',

            /**
             * The selector for the container where the active filters are shown.
             */
            activeFilterContSelector: '.filter--active-container',

            /**
             * The selector for the button which applies the filter changes.
             */
            applyFilterBtnSelector: '.filter--btn-apply',

            /**
             * The css class for active filter labels.
             */
            activeFilterCls: 'filter--active',

            /**
             * The close icon element which is used for the active filter labels.
             */
            activeFilterIconCls: 'filter--active-icon',

            /**
             * The css class for the filter panel when it is completely collapsed.
             */
            collapsedCls: 'is--collapsed',

            /**
             * The css class for the filter container when it shows only the preview of the active filters.
             */
            hasActiveFilterCls: 'is--active-filter',

            /**
             * The css class for active states.
             */
            activeCls: 'is--active',

            /**
             * The css class for disabled states.
             */
            disabledCls: 'is--disabled',

            /**
             * Selector for the element that contains the found product count.
             */
            filterCountSelector: '.filter--count',

            /**
             * Class that will be added to the apply filter button
             * when loading the results.
             */
            loadingClass: 'is--loading',

            /**
             * The characters used as a prefix to identify property field names.
             * The properties will be merged in one GET parameter.
             * For example properties with field names beginning with __f__"ID"
             * will be merged to &f=ID1|ID2|ID3|ID4 etc.
             *
             */
            propertyPrefixChar: '__',

            /**
             * The buffer time in ms to wait between each action before firing the ajax call.
             */
            bufferTime: 850,

            /**
             * The time in ms for animations.
             */
            animationSpeed: 400
        },

        /**
         * Initializes the plugin.
         */
        init: function() {
            var me = this,
                filterCount;

            me.applyDataAttributes();

            me.$filterForm = $(me.opts.filterFormSelector);
            me.$filterComponents = me.$el.find(me.opts.filterComponentSelector);
            me.$filterTrigger = me.$el.find(me.opts.filterTriggerSelector);
            me.$filterTriggerIcon = me.$filterTrigger.find(me.opts.filterTriggerIconSelector);
            me.$filterCont = me.$el.find(me.opts.filterContainerSelector);
            me.$actionForms = $(me.opts.actionFormSelector);
            me.$actionLinks = $(me.opts.actionLinkSelector);
            me.$activeFilterCont = me.$el.find(me.opts.activeFilterContSelector);
            me.$applyFilterBtn = me.$el.find(me.opts.applyFilterBtnSelector);

            me.resultCountURL = me.$filterForm.attr('data-count-ctrl');
            me.controllerURL = window.location.href.split('?')[0];
            me.resetLabel = me.$activeFilterCont.attr('data-reset-label');
            me.propertyFieldNames = [];
            me.activeFilterElements = {};
            me.categoryParams = {};
            me.urlParams = '';
            me.bufferTimeout = 0;

            me.getPropertyFieldNames();
            me.setCategoryParamsFromTopLocation();
            me.createActiveFiltersFromCategoryParams();
            me.createUrlParams();

            filterCount = Object.keys(me.activeFilterElements).length;

            me.updateFilterTriggerButton(filterCount > 1 ? filterCount - 1 : filterCount);

            me.initStateHandling();
            me.registerEvents();
        },

        /**
         * Initializes the state manager for specific device options.
         */
        initStateHandling: function() {
            var me = this,
                enterFn = $.proxy(me.onEnterMobile, me),
                exitFn = $.proxy(me.onExitMobile, me);

            StateManager.registerListener([
                {
                    state: 'xs',
                    enter: enterFn,
                    exit: exitFn
                },
                {
                    state: 's',
                    enter: enterFn,
                    exit: exitFn
                }
            ]);

            $.publish('plugin/swListingActions/onInitStateHandling', [ me ]);
        },

        /**
         * Called when entering the xs or s viewport.
         * Removes/Clears style attributes that were set in higher viewports.
         */
        onEnterMobile: function () {
            var me = this,
                opts = me.opts;

            me.$filterForm.removeAttr('style');

            me.$activeFilterCont.removeAttr('style').removeClass(opts.disabledCls);

            me.$filterCont.removeClass(opts.collapsedCls);

            me.$filterTrigger.removeClass(opts.activeCls);

            $.publish('plugin/swListingActions/onEnterMobile', [ me ]);
        },

        /**
         * Called when exiting the xs or s viewport.
         * Add the disabled class to the active filter container
         * when it has active filter elements.
         */
        onExitMobile: function () {
            var me = this;

            if (StateManager.isCurrentState(['xs', 's'])) {
                return;
            }

            if (Object.keys(me.activeFilterElements).length) {
                me.$activeFilterCont.addClass(me.opts.disabledCls);
            }

            $.publish('plugin/swListingActions/onExitMobile', [ me ]);
        },

        /**
         * Registers all necessary events.
         */
        registerEvents: function() {
            var me = this;

            me._on(me.$filterForm, 'submit', $.proxy(me.onFilterSubmit, me));
            me._on(me.$actionForms, 'submit', $.proxy(me.onActionSubmit, me));
            me._on(me.$actionLinks, 'click', $.proxy(me.onActionLink, me));
            me._on(me.$filterComponents, 'onChange', $.proxy(me.onComponentChange, me));
            me._on(me.$filterTrigger, 'click', $.proxy(me.onFilterTriggerClick, me));

            me._on($body, 'click', $.proxy(me.onBodyClick, me));

            me.$el.on(me.getEventName('click'), '.' + me.opts.activeFilterCls, $.proxy(me.onActiveFilterClick, me));

            $.publish('plugin/swListingActions/onRegisterEvents', [ me ]);
        },

        /**
         * Called by event listener on submitting the filter form.
         * Gets the serialized form data and applies it to the category params.
         *
         * @param event
         */
        onFilterSubmit: function(event) {
            event.preventDefault();

            var me = this,
                formData = me.$filterForm.serializeArray(),
                categoryParams = me.setCategoryParamsFromData(formData);

            me.applyCategoryParams(categoryParams);

            $.publish('plugin/swListingActions/onFilterSubmit', [ me, event ]);
        },

        /**
         * Called by event listener on submitting an action form.
         * Gets the serialized form data and applies it to the category params.
         *
         * @param event
         */
        onActionSubmit: function(event) {
            event.preventDefault();

            var me = this,
                $form = $(event.currentTarget),
                formData = $form.serializeArray(),
                categoryParams = me.setCategoryParamsFromData(formData, true);

            me.applyCategoryParams(categoryParams);

            $.publish('plugin/swListingActions/onActionSubmit', [ me, event ]);
        },

        /**
         * Called by event listener on clicking on an action link.
         * Reads the parameter in the href attribute and adds it to the
         * category params.
         *
         * @param event
         */
        onActionLink: function(event) {
            event.preventDefault();

            var me = this,
                $link = $(event.currentTarget),
                linkParams = $link.attr('href').split('?')[1];

            me.applyCategoryParams(
                me.setCategoryParamsFromUrlParams(linkParams)
            );

            $.publish('plugin/swListingActions/onActionLink', [ me, event ]);
        },

        /**
         * Called by event listener on clicking the filter trigger button.
         * Opens and closes the filter form panel.
         *
         * @param event
         */
        onFilterTriggerClick: function(event) {
            event.preventDefault();

            if (StateManager.isCurrentState(['xs', 's'])) {
                return;
            }

            var me = this;

            if (me.$filterCont.hasClass(me.opts.collapsedCls)) {
                me.closeFilterPanel();
            } else {
                me.openFilterPanel();
            }

            $.publish('plugin/swListingActions/onFilterTriggerClick', [ me, event ]);
        },

        /**
         * Closes all filter panels if the user clicks anywhere else.
         *
         * @param event
         */
        onBodyClick: function(event) {
            var me = this,
                $target = $(event.target);

            if (!$target.is(me.opts.filterComponentSelector + ', ' + me.opts.filterComponentSelector + ' *')) {
                $.each(me.$filterComponents, function(index, item) {
                    $(item).data('plugin_swFilterComponent').close();
                });
            }

            $.publish('plugin/swListingActions/onBodyClick', [ me, event ]);
        },

        /**
         * Called by event listener on the change event of the
         * single filter components. Applies the changes of the
         * component values to the category params.
         *
         * @param event
         */
        onComponentChange: function(event) {
            var me = this,
                formData = me.$filterForm.serializeArray(),
                categoryParams = me.setCategoryParamsFromData(formData),
                urlParams = me.createUrlParams(categoryParams);

            me.createActiveFiltersFromCategoryParams(categoryParams);

            me.$applyFilterBtn.addClass(me.opts.loadingClass);

            me.buffer($.proxy(me.getFilterResult, me, urlParams), me.opts.bufferTime);

            $.publish('plugin/swListingActions/onComponentChange', [ me, event ]);
        },

        /**
         * Called by event listener on clicking an active filter label.
         * It removes the clicked filter param form the set of active filters
         * and updates the specific component.
         *
         * @param event
         */
        onActiveFilterClick: function(event) {
            var me = this,
                $activeFilter = $(event.currentTarget),
                param = $activeFilter.attr('data-filter-param'),
                isMobile = StateManager.isCurrentState(['xs', 's']);

            if (param == 'reset') {
                $.each(me.activeFilterElements, function(key) {
                    me.removeActiveFilter(key);
                    me.resetFilterProperty(key);
                });

                if (!isMobile && !me.$filterCont.hasClass(me.opts.collapsedCls)) {
                    me.applyCategoryParams();
                }
            } else if (isMobile || !me.$activeFilterCont.hasClass(me.opts.disabledCls)) {
                me.removeActiveFilter(param);
                me.resetFilterProperty(param);
            }

            $.publish('plugin/swListingActions/onActiveFilterClick', [ me, event ]);
        },

        getPropertyFieldNames: function() {
            var me = this;

            $.each(me.$filterComponents, function(index, item) {
                var $comp = $(item),
                    type = $comp.attr('data-filter-type'),
                    fieldName = $comp.attr('data-field-name');

                if ((type == 'value-list' || type == 'value-tree' || type == 'media') &&
                    me.propertyFieldNames.indexOf(fieldName) == -1) {
                    me.propertyFieldNames.push(fieldName);
                }
            });

            $.publish('plugin/swListingActions/onGetPropertyFieldNames', [ me, me.propertyFieldNames ]);

            return me.propertyFieldNames;
        },

        /**
         * Converts given form data to the category parameter object.
         * You can choose to either extend or override the existing object.
         *
         * @param formData
         * @param extend
         * @returns {*}
         */
        setCategoryParamsFromData: function(formData, extend) {
            var me = this,
                tempParams = {};

            $.each(formData, function(index, item) {
                if (item['value']) tempParams[item['name']] = item['value'];
            });

            if (extend) {
                return $.extend(me.categoryParams, tempParams);
            }

            me.categoryParams = tempParams;

            $.publish('plugin/swListingActions/onSetCategoryParamsFromData', [ me, tempParams ]);

            return tempParams;
        },

        /**
         * Converts top location parameters to the category parameter object.
         *
         * @returns {*}
         */
        setCategoryParamsFromTopLocation: function () {
            var me = this,
                urlParams = window.location.search.substr(1),
                categoryParams = me.setCategoryParamsFromUrlParams(urlParams);

            $.publish('plugin/swListingActions/onSetCategoryParamsFromData', [ me, categoryParams ]);

            return categoryParams;
        },

        /**
         * Converts url parameters to the category parameter object.
         *
         * @param urlParamString
         * @returns {{}|*}
         */
        setCategoryParamsFromUrlParams: function (urlParamString) {
            var me = this,
                categoryParams,
                params;

            if (urlParamString.length <= 0) {
                categoryParams = {};

                $.publish('plugin/swListingActions/onSetCategoryParamsFromUrlParams', [ me, categoryParams ]);

                return categoryParams;
            }

            categoryParams = me.categoryParams;
            params = urlParamString.split('&');

            $.each(params, function (index, item) {
                var param = item.split('=');

                param = $.map(param, function (val) {
                    val = val.replace(/\+/g, '%20');
                    return decodeURIComponent(val);
                });

                if (param[1] == 'reset') {
                    delete categoryParams[param[0]];
                } else if (me.propertyFieldNames.indexOf(param[0]) != -1) {
                    var properties = param[1].split('|');

                    $.each(properties, function (index, property) {
                        categoryParams[me.opts.propertyPrefixChar + param[0] + me.opts.propertyPrefixChar + property] = property;
                    });
                } else {
                    categoryParams[param[0]] = param[1];
                }
            });

            $.publish('plugin/swListingActions/onSetCategoryParamsFromUrlParams', [ me, categoryParams ]);

            return categoryParams;
        },

        /**
         * Converts the category parameter object to url parameters
         * and applies the url parameters to the current top location.
         *
         * @param categoryParams
         */
        applyCategoryParams: function(categoryParams) {
            var me = this,
                params = categoryParams || me.categoryParams,
                urlParams = me.createUrlParams(params);

            me.applyUrlParams(urlParams);

            $.publish('plugin/swListingActions/onApplyCategoryParams', [ me, categoryParams ]);
        },

        /**
         * Converts the category parameter object to url parameters.
         *
         * @param categoryParams
         * @returns {string}
         */
        createUrlParams: function(categoryParams) {
            var me = this,
                catParams = categoryParams || me.categoryParams,
                params = me.cleanParams(catParams),
                filterList = [];

            $.each(params, function(key, value) {
                filterList.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
            });

            me.urlParams = '?' + filterList.join('&');

            $.publish('plugin/swListingActions/onCreateUrlParams', [me, me.urlParams]);

            return me.urlParams;
        },

        cleanParams: function(params) {
            var me = this,
                propertyParams = {};

            $.each(params, function(key, value) {
                if (key.substr(0, 2) == me.opts.propertyPrefixChar) {
                    var propertyKey = key.split(me.opts.propertyPrefixChar)[1];

                    if (propertyParams[propertyKey] !== undefined) {
                        propertyParams[propertyKey] += '|' + value;
                    } else {
                        propertyParams[propertyKey] = value;
                    }
                } else {
                    propertyParams[key] = value;
                }
            });

            return propertyParams;
        },

        /**
         * Applies given url params to the top location.
         *
         * @param urlParams | String
         */
        applyUrlParams: function(urlParams) {
            var me = this,
                params = urlParams || me.urlParams;

            window.location.href = me.getListingUrl(params, false);

            $.publish('plugin/swListingActions/onApplyUrlParams', [ me, urlParams ]);
        },

        /**
         * Returns the full url path to the listing
         * including all current url params.
         *
         * @param urlParams
         * @param encode | Boolean
         * @returns {*}
         */
        getListingUrl: function(urlParams, encode) {
            var me = this,
                params = urlParams || me.urlParams,
                url;

            if (encode) {
                url = encodeURI(me.controllerURL + params);
            } else {
                url = me.controllerURL + params;
            }

            $.publish('plugin/swListingActions/onGetListingUrl', [ me, url, urlParams, encode ]);

            return url;
        },

        /**
         * Buffers a function by the given buffer time.
         *
         * @param func
         * @param bufferTime
         */
        buffer: function(func, bufferTime) {
            var me = this;

            if (me.bufferTimeout) {
                clearTimeout(me.bufferTimeout);
            }

            me.bufferTimeout = setTimeout(func, bufferTime);

            $.publish('plugin/swListingActions/onBuffer', [ me, me.bufferTimeout, func, bufferTime ]);
        },

        /**
         * Resets the current buffer timeout.
         */
        resetBuffer: function() {
            var me = this;

            me.bufferTimeout = 0;

            $.publish('plugin/swListingActions/onResetBuffer', [ me, me.bufferTimeout ]);
        },

        /**
         * Gets the counted result of found products
         * with the current applied category parameters.
         * Updates the filter submit button on success.
         *
         * @param urlParams
         */
        getFilterResult: function(urlParams) {
            var me = this,
                params = urlParams || me.urlParams;

            me.resetBuffer();

            $.ajax({
                type: 'get',
                url: me.resultCountURL + params,
                success: function(response) {
                    me.$applyFilterBtn.removeClass(me.opts.loadingClass);

                    me.updateFilterButton(response.totalCount);

                    $.publish('plugin/swListingActions/onGetFilterResultFinished', [ me, response, params ]);
                }
            });

            $.publish('plugin/swListingActions/onGetFilterResult', [ me, params ]);
        },

        /**
         * Updates the layout of the filter submit button
         * with the new count of found products.
         *
         * @param count
         */
        updateFilterButton: function(count) {
            var me = this;

            me.$applyFilterBtn.find(me.opts.filterCountSelector).html(count);

            if (count <= 0) {
                me.$applyFilterBtn.attr('disabled', 'disabled');
            } else {
                me.$applyFilterBtn.removeAttr('disabled');
            }

            $.publish('plugin/swListingActions/onUpdateFilterButton', [ me, count ]);
        },

        /**
         * Updates the layout of the filter trigger button
         * on mobile viewports with the current count of active filters.
         *
         * @param activeFilterCount
         */
        updateFilterTriggerButton: function(activeFilterCount) {
            var me = this;

            me.$filterTriggerIcon.html(activeFilterCount || '');

            $.publish('plugin/swListingActions/onUpdateFilterTriggerButton', [ me, activeFilterCount ]);
        },

        /**
         * Creates the labels for active filters from the category params.
         *
         * @param categoryParams
         */
        createActiveFiltersFromCategoryParams: function(categoryParams) {
            var me = this,
                count = 0,
                params = categoryParams || me.categoryParams;

            $.each(me.activeFilterElements, function(key) {
                if (params[key] === undefined || params[key] == 0) {
                    me.removeActiveFilter(key);
                }
            });

            $.each(params, function(key, value) {
                me.createActiveFilter(key, value);
            });

            $.each(me.activeFilterElements, function() {
                count++;
            });

            if (count > 1) {
                me.createActiveFilterElement('reset', me.resetLabel);
            }

            me.$filterCont.toggleClass(me.opts.hasActiveFilterCls, (count > 0));
            me.$activeFilterCont.toggleClass(me.opts.disabledCls, !me.$filterCont.hasClass(me.opts.collapsedCls));

            $.publish('plugin/swListingActions/onCreateActiveFiltersFromCategoryParams', [ me, categoryParams ]);
        },

        /**
         * Creates an active filter label for the given parameter.
         * If the label for the given parameter already
         * exists it will be updated.
         *
         * @param param
         * @param value
         */
        createActiveFilter: function(param, value) {
            var me = this,
                label = me.createActiveFilterLabel(param, value);

            if (label !== undefined && label.length) {
                if (me.activeFilterElements[param] !== undefined) {
                    me.updateActiveFilterElement(param, label);
                } else {
                    me.createActiveFilterElement(param, label);
                }
            }

            $.publish('plugin/swListingActions/onCreateActiveFilter', [ me, param, value ]);
        },

        /**
         * Creates the DOM element for an active filter label.
         *
         * @param param
         * @param label
         */
        createActiveFilterElement: function(param, label) {
            var me = this;

            me.activeFilterElements[param] = $('<span>', {
                'class': me.opts.activeFilterCls,
                'html': me.getLabelIcon() + label,
                'data-filter-param': param
            }).appendTo(me.$activeFilterCont);

            $.publish('plugin/swListingActions/onCreateActiveFilterElement', [ me, param, label ]);
        },

        /**
         * Updates the layout of an existing filter label element.
         *
         * @param param
         * @param label
         */
        updateActiveFilterElement: function(param, label) {
            var me = this;

            me.activeFilterElements[param].html(me.getLabelIcon() + label);

            $.publish('plugin/swListingActions/onUpdateActiveFilterElement', [ me, param, label ]);
        },

        /**
         * Removes an active filter label from the set and from the DOM.
         *
         * @param param
         */
        removeActiveFilter: function(param) {
            var me = this;

            me.activeFilterElements[param].remove();

            delete me.activeFilterElements[param];

            $.publish('plugin/swListingActions/onRemoveActiveFilter', [ me, param ]);
        },

        /**
         * Resets a filter parameter and updates
         * the component based on the component type.
         *
         * @param param
         */
        resetFilterProperty: function(param) {
            var me = this,
                $input,
                rangeSlider;

            if (param == 'rating') {
                me.$el.find('#star--reset').prop('checked', true).trigger('change');
            } else {
                $input = me.$el.find('[name="' + me.escapeDoubleQuotes(param) + '"]');
                if ($input.is('[data-range-input]')) {
                    rangeSlider = $input.parents('[data-range-slider="true"]').data('plugin_swRangeSlider');
                    rangeSlider.reset($input.attr('data-range-input'));
                } else {
                    $input.removeAttr('checked').trigger('change');
                }
            }

            $.publish('plugin/swListingActions/onResetFilterProperty', [ me, param ]);
        },

        /**
         * Creates the correct label content for an active
         * filter label based on the component type.
         *
         * @param param
         * @param value
         * @returns {string}
         */
        createActiveFilterLabel: function(param, value) {
            var me = this,
                $label,
                labelText = '',
                valueString = value + '';

            if (param == 'rating' && value > 0) {
                labelText = me.createStarLabel(value);
            } else {
                $label = me.$filterForm.find('label[for="' + me.escapeDoubleQuotes(param) + '"]');

                if ($label.is('[data-range-label]')) {
                    labelText = $label.prev('span').html() + $label.html();
                } else if ($label.find('img').length) {
                    labelText = $label.find('img').attr('alt');
                } else if (value > 0 || valueString.length > 0) {
                    labelText = $label.html();
                }
            }

            $.publish('plugin/swListingActions/onCreateActiveFilterLabel', [ me, labelText, param, value ]);

            return labelText;
        },

        /**
         * Only escapes a " if it's not already escaped
         * @param string str
         * @returns string
         */
        escapeDoubleQuotes: function (str) {
            return str.replace(/\\([\s\S])|(")/g, '\\$1$2');
        },

        /**
         * Creates the label content for the special rating component.
         *
         * @param stars | Integer
         * @returns {string}
         */
        createStarLabel: function(stars) {
            var me = this,
                label = '',
                i = 0;

            for (i; i < 5; i++) {
                if (i < stars) {
                    label += '<i class="icon--star"></i>';
                } else {
                    label += '<i class="icon--star-empty"></i>';
                }
            }

            $.publish('plugin/swListingActions/onCreateStarLabel', [ me, label, stars ]);

            return label;
        },

        /**
         * Returns the html string of the delete icon
         * for an active filter label.
         *
         * @returns {string}
         */
        getLabelIcon: function() {
            var me = this,
                icon = '<span class="' + me.opts.activeFilterIconCls + '"></span>';

            $.publish('plugin/swListingActions/onCreateStarLabel', [ me, icon ]);

            return icon;
        },

        /**
         * Opens the filter form panel based on the current state.
         */
        openFilterPanel: function() {
            var me = this;

            if (!me.$filterCont.hasClass(me.opts.hasActiveFilterCls)) {
                me.$activeFilterCont.slideDown(me.opts.animationSpeed);
            }

            me.$filterForm.slideDown(me.opts.animationSpeed);
            me.$activeFilterCont.removeClass(me.opts.disabledCls);
            me.$filterCont.addClass(me.opts.collapsedCls);
            me.$filterTrigger.addClass(me.opts.activeCls);

            $.publish('plugin/swListingActions/onOpenFilterPanel', [ me ]);
        },

        /**
         * Closes the filter form panel based on the current state.
         */
        closeFilterPanel: function() {
            var me = this;

            if (!me.$filterCont.hasClass(me.opts.hasActiveFilterCls)) {
                me.$activeFilterCont.slideUp(me.opts.animationSpeed);
            }

            me.$filterForm.slideUp(me.opts.animationSpeed);
            me.$activeFilterCont.addClass(me.opts.disabledCls);
            me.$filterCont.removeClass(me.opts.collapsedCls);
            me.$filterTrigger.removeClass(me.opts.activeCls);

            $.publish('plugin/swListingActions/onCloseFilterPanel', [ me ]);
        },

        /**
         * Destroys the plugin.
         */
        destroy: function() {
            var me = this;

            me.$el.off(me.getEventName('click'), '.' + me.opts.activeFilterCls);

            me._destroy();
        }
    });
})(jQuery, window, StateManager, undefined);
