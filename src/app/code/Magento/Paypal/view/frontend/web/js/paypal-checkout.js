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
    "mage/mage"
], function($){
    "use strict";

    $.widget('mage.paypalCheckout', {
        /**
         * Initialize store credit events
         * @private
         */
        _create: function () {
            this.element.on('click', '[data-action="checkout-form-submit"]', $.proxy(function (e) {
                var returnUrl = $(e.target).data('checkout-url');
                if (this.options.confirmUrl && this.options.confirmMessage) {
                    if (window.confirm(this.options.confirmMessage)) {
                        returnUrl = this.options.confirmUrl;
                    }
                }
                if (this.options.isCatalogProduct) {
                    // find the form from which the button was clicked
                    var form  = $(this.options.shortcutContainerClass).closest("form");
                    $(form).find(this.options.paypalCheckoutSelector).val(returnUrl);
                    $(form).submit();
                } else {
                    $.mage.redirect(returnUrl);
                }
            }, this));
        }
    });
});