/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define([
    'underscore',
    'Magento_Ui/js/lib/ko/scope',
    'Magento_Ui/js/lib/component'
], function(_, Scope, Component) {
    'use strict';
    
    var defaults = {
        dirs: {
            asc: 'sort-arrow-asc',
            desc: 'sort-arrow-desc'
        },
        params: {
            dir: 'sorting',
            items: ['field', 'direction']
        },
        initialDir: 'asc',
        noSort: 'not-sort',
        templateExtender: 'sortable'
    };

    var Sorting = Scope.extend({

        /**
         * Extends instance with defaults and config, initializes observable properties.
         * Updates storage with current state of instance. 
         * @param  {Object} config
         */
        initialize: function(config) {
            _.extend(this, defaults, config);
            
            this.initObservable()
                .attachTemplateExtender()
                .pushParams();
        },

        /**
         * Initializes observable properties of instance.
         * @returns {Sorting} Chainable.
         */
        initObservable: function(){
            this.observe({
                field:      this.field,
                direction:  this.direction
            });

            return this;
        },

        /**
         * Attaches it's template to provider.dump's extenders
         * @returns {Sorting} Chainable.
         */
        attachTemplateExtender: function () {
            var provider    = this.provider.dump,
                extenders   = provider.get('extenders');
                
            extenders.push({
                path: this.templateExtender,
                name: this.name,
                as:   'sorting'
            });

            provider.trigger('update:extenders', extenders);

            return this;
        },

        /**
         * Generates css class for indicating sorting state for field. 
         * @param {String} id - identifier of field to be sorted
         * @returns {String} - css class.
         */
        setClass: function(id) {
            return this.isSorted(id) ?
                this.dirs[this.direction()] :
                this.noSort;
        },

        /**
         * Toggles observable dir property betweeen 'asc' and 'desc' values.
         */
        toggleDirection: function() {
            var dir = this.direction;

            dir(dir() === 'asc' ? 'desc' : 'asc');
        },

        /**
         * Sets currently sorted field and initial sorting type for it.
         * @param {String} id - identifier of field to be sorted
         */
        setSort: function(id) {
            this.field(id);
            this.direction(this.initialDir);
        },

        /**
         * Sorts by field and reloads storage.
         * @param {(String|Number)} id - Identifier of field to be sorted.
         */
        sortBy: function(id) {
            this.isSorted(id) ?
                this.toggleDirection() :
                this.setSort(id);

            this.reload();
        },

        /**
         * Checks if the field is currently sorted.
         * @param {String} id - identifier of field to be sorted
         * @returns {Boolean} true, if target field is sorted already, false otherwise
         */
        isSorted: function(id) {
            return id === this.field();
        },

        /**
         * Returns function to handle user's click (workaround for knockout.js).
         * @param {Object} field
         * @returns {Function} - click handler
         */
        onClick: function(field) {
            return this.sortBy.bind(this, field.index);
        }
    });

    return Component({
        constr: Sorting
    });
});