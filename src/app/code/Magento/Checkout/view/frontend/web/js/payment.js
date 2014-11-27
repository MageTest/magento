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
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "jquery/template"
], function($){
    'use strict';

    $.widget('mage.payment', {
        options: {
            continueSelector: '#payment-continue',
            methodsContainer: '#payment-methods',
            minBalance: 0.0001,
            tmpl: '<input id="hidden-free" type="hidden" name="payment[method]" value="free">'
        },

        _create: function() {
            this.element.find('dd [name^="payment["]').prop('disabled', true).end()
                .on('click', this.options.continueSelector, $.proxy(this._submitHandler, this))
                .on('updateCheckoutPrice', $.proxy(function(event, data) {
                //updating the checkoutPrice
                if (data.price) {
                    this.options.checkoutPrice += data.price;
                }
                //updating total price
                if (data.totalPrice) {
                    data.totalPrice = this.options.checkoutPrice;
                }
                if (this.options.checkoutPrice < this.options.minBalance) {
                    // Add free input field, hide and disable unchecked checkbox payment method and all radio button payment methods
                    this._disablePaymentMethods();
                } else {
                    // Remove free input field, show all payment method
                    this._enablePaymentMethods();
                }
            }, this))
                .on('click', 'dt input:radio', $.proxy(this._paymentMethodHandler, this));

            if (this.options.checkoutPrice < this.options.minBalance) {
                this._disablePaymentMethods();
            } else {
                this._enablePaymentMethods();
            }
        },

        /**
         * Display payment details when payment method radio button is checked
         * @private
         * @param e
         */
        _paymentMethodHandler: function(e) {
            var element = $(e.target),
                parentsDl = element.closest('dl');
            parentsDl.find('dt input:radio').prop('checked', false);
            parentsDl.find('.items').hide().find('[name^="payment["]').prop('disabled', true);
            element.prop('checked', true).parent().nextUntil('dt').find('.items').show().find('[name^="payment["]').prop('disabled', false);
        },

        /**
         * make sure one payment method is selected
         * @private
         * @return {Boolean}
         */
        _validatePaymentMethod: function() {
            var methods = this.element.find('[name^="payment["]'),
                isValid = false;
            if (methods.length === 0) {
                alert($.mage.__("We can't complete your order because you don't have a payment method available."));
            } else if (this.options.checkoutPrice < this.options.minBalance) {
                isValid = true;
            } else if (methods.filter('input:radio:checked').length) {
                isValid = true;
            } else {
                alert($.mage.__('Please specify payment method.'));
            }
            return isValid;
        },

        /**
         * Disable and enable payment methods
         * @private
         */
        _disablePaymentMethods: function() {
            this.element.find('input[name="payment[method]"]').prop('disabled', true).end()
                .find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', true).parent().hide();
            this.element.find('[name="payment[method]"][value="free"]').parent('dt').remove();
            this.element.find(this.options.methodsContainer).hide().find('[name^="payment["]').prop('disabled', true);
            $.tmpl(this.options.tmpl).appendTo(this.element);
        },

        /**
         * Enable and enable payment methods
         * @private
         */
        _enablePaymentMethods: function() {
            this.element.find('input[name="payment[method]"]').prop('disabled', false).end()
                .find('input[name="payment[method]"][value="free"]').remove().end()
                .find('dt input:radio:checked').trigger('click').end()
                .find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', false).parent().show();
            this.element.find(this.options.methodsContainer).show();
        },

        /**
         * Validate  before form submit
         * @private
         */
        _submitHandler: function(e) {
            e.preventDefault();
            if (this._validatePaymentMethod()) {
                this.element.submit();
            }
        }
    });
});