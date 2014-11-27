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
    'jquery'
], function ($) {

    function KeyboardHandler() {
        var focusState = false;
        var tabFocusClass = 'focusin';
        return {
            init: SmartKeyboardFocus
        };

        function SmartKeyboardFocus() {
            $(document).on('keydown keypress', function (event) {
                if (event.which === 9 && !focusState) {
                    $('body')
                        .on('focusin', onFocusInHandler)
                        .on('click', onClickHandler);
                }
            });
        }

        function onFocusInHandler() {
            focusState = true;
            $('body').addClass(tabFocusClass)
                .off('focusin', onFocusInHandler);
        }

        function onClickHandler(event) {
            focusState = false;
            $('body').removeClass(tabFocusClass)
                .off('click', onClickHandler);
        }
    }

    return new KeyboardHandler();
});
