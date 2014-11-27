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
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function($){

    $.widget('vde.vdeImageSizing', {
        options: {
            restoreDefaultDataEvent: 'restoreDefaultData',
            saveFormEvent: 'saveForm',
            maxSizeValue: 500,
            formUrl: null,
            formId: null,
            imageRatioClass: null,
            imageWidthClass: null,
            imageHeightClass: null,
            messagesContainer: null
        },

        /**
         * Initialize widget
         * @private
         */
        _create: function() {
            this._bind();
            this._initRatioSwitchers();
        },

        /**
         * Bind event handlers
         * @private
         */
        _bind: function() {
            var body = $('body');
            body.on(this.options.restoreDefaultDataEvent, $.proxy(this._onRestoreDefaultData, this));
            body.on(this.options.saveFormEvent, $.proxy(this._onSaveForm, this));
            $(this.options.formId + " input[type='text']").live('keyup',  $.proxy(this._validateInput, this));
            $(this.options.formId).on('submit', function(){return false;});
            $(this.options.imageRatioClass).on("change", $.proxy(this._onRationSwitcher, this));
        },

        /**
         * Find all ration switcher and manage theme
         * @private
         */
        _initRatioSwitchers: function () {
            $(this.options.imageRatioClass).each($.proxy(this._eachRationSwitcher, this));
        },

        /**
         * Init ratio switcher by index
         * @param index
         * @private
         */
        _eachRationSwitcher: function(index) {
            this._switchRation($(this.options.imageRatioClass).eq(index));
        },

        /**
         * Event handler on on/off ratio
         * @param event
         * @private
         */
        _onRationSwitcher: function(event) {
            this._switchRation(event.target);
        },

        /**
         * Manage ratio switcher state
         * @param element
         * @private
         */
        _switchRation: function(element) {
            $(element).attr("checked") == "checked" ? this._switchOnRation(element) : this._switchOffRatio(element) ;
            $(element).closest('.choice').toggleClass('checked', $(element).prop('checked'));
        },

        /**
         * Switch off ratio
         * @param elementRatio
         * @private
         */
        _switchOffRatio: function(elementRatio) {
            var elementHeight = $(elementRatio).closest("fieldset").find(this.options.imageHeightClass);
            var elementWidth = $(elementRatio).closest("fieldset").find(this.options.imageWidthClass);
            $(elementHeight).removeAttr('readonly');
            $(elementWidth).unbind('change');
        },

        /**
         * Switch on ratio
         * @param elementRatio
         * @private
         */
        _switchOnRation: function(elementRatio) {
            var elementHeight = $(elementRatio).closest("fieldset").find(this.options.imageHeightClass);
            var elementWidth = $(elementRatio).closest("fieldset").find(this.options.imageWidthClass);
            if ($(elementHeight).val() != "") {
                $(elementHeight).val($(elementWidth).val());
                $(elementWidth).bind('change', $.proxy(this._onChangeWidth, this));
            }
            $(elementHeight).attr('readonly', 'readonly');
        },

        /**
         * Event handler on change image width if ratio on
         * @param event
         * @private
         */
        _onChangeWidth: function(event) {
            var elementHeight = $(event.target).closest("fieldset").find(this.options.imageHeightClass);
            $(elementHeight).val($(event.target).val());
        },

        /**
         * Validate width and height input
         * @param event
         * @param data
         * @private
         */
        _validateInput: function(event, data)
        {
            var value = $(event.currentTarget).val();
            value = parseInt(value);
            value = isNaN(value) ? '' : value;
            value = value > this.options.maxSizeValue ? this.options.maxSizeValue : value;
            $(event.currentTarget).val(value);
            $(event.currentTarget).trigger('change');
        },

        /**
         * Restore default data for one item
         * @param event
         * @param data
         * @private
         */
        _onRestoreDefaultData: function(event, data) {
            for (var elementId in data) {
                var element = $(document.getElementById(elementId));
                if (element.is("input[type='checkbox']")) {
                    data[elementId] ? element.attr('checked', 'checked') : element.removeAttr('checked');
                    element.trigger('change');
                } else {
                    element.val(data[elementId] ? data[elementId] : '');
                }
            }
        },

        /**
         * Ajax saving form
         * @param event
         * @param data
         * @private
         */
        _onSaveForm: function(event, data) {
            $.ajax({
                url: this.options.formUrl,
                type: 'POST',
                data: $(this.options.formId).serialize(),
                dataType: 'json',
                showLoader: false,
                success: $.proxy(function(response) {
                    this.element.trigger('addMessage', {
                        containerId : this.options.messagesContainer,
                        message : response.message
                    });
                    this.element.trigger('refreshIframe');
                }, this),
                error: $.proxy(function() {
                    alert($.mage.__('Sorry, there was an unknown error.'));
                }, this)
            });
        }
    });

});