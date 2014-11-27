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
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template"
], function($){
    "use strict";

    $.widget('mage.rowBuilder', {

        /**
         * options with default values for setting up the template
         */
        options: {
            //Default template options
            rowTemplate: '#template-registrant',
            rowContainer: '#registrant-container',
            //Row index used by the template rows.
            rowIndex: 0,
            //Row count: Should not be set externally
            rowCount: 0,
            rowParentElem: '<li></li>',
            rowContainerClass: 'fields',
            addRowBtn: '#add-registrant-button',
            btnRemoveIdPrefix: 'btn-remove',
            btnRemoveSelector: '.btn-remove',
            rowIdPrefix: 'row',
            //This class is added to rows added after the first one. Adds the dotted separator
            additionalRowClass: 'add-row',
            /*
             This is provided during widget instantiation. eg :
             formDataPost : {"formData":formData,"templateFields":['field1-name','field2-name'] }
             -"formData" is the multi-dimensional array of form field values : [['a','b'],['c','b']]
             received from the server and encoded
             -"templateFields" are the input fields in the template with index suffixed after the field name
             eg field1-name{index}
             */
            formDataPost: null,
            //Default selectors for add element of a template
            addEventSelector: 'button',
            //Default selectors for remove markup elements of a template
            remEventSelector: 'a',
            //This option allows adding first row delete option and a row separator
            hideFirstRowAddSeparator: true,
            //Max rows - This option should be set when instantiating the widget
            maxRows: 1000,
            maxRowsMsg: '#max-registrant-message'
        },

        /**
         * Initialize create
         * @private
         */
        _create: function() {
            this.options.rowCount = this.options.rowIndex = 0;
            //On document ready related tasks
            $($.proxy(this.ready, this));

            //Binding template-wide events handlers for adding and removing rows
            this.element.on('click', this.options.addEventSelector + this.options.addRowBtn, $.proxy(this.handleAdd, this));
            this.element.on('click', this.options.remEventSelector + this.options.btnRemoveSelector, $.proxy(this.handleRemove, this));
        },

        /**
         * Initialize template
         * @public
         */
        ready: function() {
            if (this.options.formDataPost && this.options.formDataPost.formData && this.options.formDataPost.formData.length) {
                this.processFormDataArr(this.options.formDataPost);
            } else if (this.options.rowIndex === 0 && this.options.maxRows !== 0) {
                //If no form data , then add default row
                this.addRow(0);
            }
        },

        /**
         * Process and loop through all row data to create preselected values. This is used for any error on submit.
         * For complex implementations the inheriting widget can override this behavior
         * @public
         * @param {Object} formDataArr
         */
        processFormDataArr: function(formDataArr) {
            var formData = formDataArr.formData,
                templateFields = formDataArr.templateFields;
            for (var i = this.options.rowIndex = 0; i < formData.length; this.options.rowIndex = i++) {
                this.addRow(i);
                var formRow = formData[i];
                for (var j = 0; j < formRow.length; j++) {
                    this.setFieldById(templateFields[j] + i, formRow[j]);
                }
            }

        },

        /**
         * Initialize and create markup for template row. Add it to the parent container.
         * The template processing will substitute row index at all places marked with _index_ in the template
         * using the template
         * @public
         * @param {Number} index - current index/count of the created template. This will be used as the id
         * @return {*}
         */
        addRow: function(index) {
            var row = $(this.options.rowParentElem);
            row.addClass(this.options.rowContainerClass).attr('id', this.options.rowIdPrefix + index);
            $(this.options.rowTemplate).tmpl([
                {_index_: index}
            ]).appendTo(row);
            $(this.options.rowContainer).append(row);
            row.addClass(this.options.additionalRowClass);
            //Remove 'delete' link and additionalRowClass for first row
            if (this.options.rowIndex === 0 && this.options.hideFirstRowAddSeparator) {
                $('#' + this._esc(this.options.btnRemoveIdPrefix) + '0').remove();
                $('#' + this._esc(this.options.rowIdPrefix) + '0').removeClass(this.options.additionalRowClass);
            }
            this.maxRowCheck(++this.options.rowCount);
            return row;
        },

        /**
         * Remove return item information row
         * @public
         * @param {*} rowIndex - return item information row index
         * @return {boolean}
         */
        removeRow: function(rowIndex) {
            $('#' + this._esc(this.options.rowIdPrefix) + rowIndex).remove();
            this.maxRowCheck(--this.options.rowCount);
            return false;
        },

        /**
         * Function to check if maximum rows are exceeded and render/hide maxMsg and Add btn
         * @public
         * @param rowIndex
         */
        maxRowCheck: function(rowIndex) {
            var addRowBtn = $(this.options.addRowBtn),
                maxRowMsg = $(this.options.maxRowsMsg);
            //liIndex starts from 0
            if (rowIndex >= this.options.maxRows) {
                addRowBtn.hide();
                maxRowMsg.show();
            } else if (addRowBtn.is(":hidden")) {
                addRowBtn.show();
                maxRowMsg.hide();
            }
        },

        /**
         * Set the value on given element
         * @public
         * @param {string} domId
         * @param {string} value
         */
        setFieldById: function(domId, value) {
            var x = $('#' + this._esc(domId));
            if (x.length) {
                if (x.is(':checkbox')) {
                    x.attr('checked', true);
                } else if (x.is('option')) {
                    x.attr('selected', 'selected');
                } else {
                    x.val(value);
                }
            }
        },

        /**
         * Delegated handler for adding a row
         * @public
         * @return {boolean}
         */
        handleAdd: function() {
            this.addRow(++this.options.rowIndex);
            return false;
        },

        /**
         * Delegated handler for removing a selected row
         * @public
         * @param {Object} e - Native event object
         * @return {boolean}
         */
        handleRemove: function(e) {
            this.removeRow($(e.currentTarget).closest("[id^='" + this.options.btnRemoveIdPrefix + "']")
                .attr('id').replace(this.options.btnRemoveIdPrefix, ''));
            return false;
        },

        /*
         * Utility function to add escape chars for jquery selector strings
         * @private
         * @param str - string to be processed
         * @return {string}
         */
        _esc: function(str) {
            return str ? str.replace(/([ ;&,.+*~\':"!\^$\[\]()=>|\/@])/g, '\\$1') : str;
        }
    });

});