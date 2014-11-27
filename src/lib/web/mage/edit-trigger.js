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
/*jshint browser:true jquery:true */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "jquery/ui",
            "jquery/template"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";

    $.widget("mage.editTrigger", {
        options: {
            img: '',
            alt: '[TR]',
            template: '#translate-inline-icon',
            zIndex: 2000,
            editSelector: '[data-translate]',
            delay: 2000,
            offsetTop: -3,
            singleElement: true
        },
        /**
         * editTriger creation
         * @protected
         */
        _create: function() {
            $(this.options.template).template(this.widgetName);
            this._initTrigger();
            this._bind();
        },
        _getCss: function() {
            return {
                position: 'absolute',
                cursor: 'pointer',
                display: 'none',
                'z-index': this.options.zIndex
            };
        },
        _createTrigger: function(appendTo) {
            return $.tmpl(this.widgetName, this.options)
                .css(this._getCss())
                .data('role', 'edit-trigger-element')
                .appendTo(appendTo);
        },
        _initTrigger: function() {
            this.trigger = this._createTrigger($('body'));
        },
        /**
         * Bind on mousemove event
         * @protected
         */
        _bind: function() {
            this.trigger.on('click.' + this.widgetName, $.proxy(this._onClick, this));
            this.element.on('mousemove.' + this.widgetName, $.proxy(this._onMouseMove, this));
        },
        /**
         * Show editTriger
         */
        show: function() {
            if (this.trigger.is(':hidden')) {
                this.trigger.show();
            }
        },
        /**
         * Hide editTriger
         */
        hide: function() {
            this.currentTarget = null;
            if (this.trigger && this.trigger.is(':visible')) {
                this.trigger.hide();
            }
        },
        /**
         * Set editTriger position
         * @protected
         */
        _setPosition: function(el) {
            var offset = el.offset();
            this.trigger.css({
                top: offset.top + el.outerHeight() + this.options.offsetTop,
                left: offset.left
            });
        },
        /**
         * Show/hide trigger on mouse move
         * @param {Object} event object
         * @protected
         */
        _onMouseMove: function(e) {
            var target = $(e.target);
            target = target.is(this.trigger) || target.is(this.options.editSelector) ?
                target :
                target.parents(this.options.editSelector).first();

            if (target.size()) {
                if (!target.is(this.trigger)) {
                    this._setPosition(target);
                    this.currentTarget = target;
                }
                this.show();
            } else {
                this.hide();
            }
        },
        /**
         * Trigger event "edit" on element for translate
         * @param {Object} event object
         * @protected
         */
        _onClick: function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $(this.currentTarget).trigger('edit.' + this.widgetName);
            this.hide(true);
        },
        /**
         * Destroy editTriger
         */
        destroy: function() {
            this.trigger.remove();
            this.element.off('.' + this.widgetName);
            return $.Widget.prototype.destroy.call(this);
        }
    });

    /**
     * Extention for widget editTrigger - hide trigger with delay
     */
    var editTriggerPrototype = $.mage.editTrigger.prototype;
    $.widget("mage.editTrigger", $.extend({}, editTriggerPrototype, {
        /**
         * Added clear timeout on trigger show
         */
        show: function() {
            editTriggerPrototype.show.apply(this, arguments);
            if(this.options.delay){
                this._clearTimer();
            }
        },
        /**
         * Added setTimeout on trigger hide
         */
        hide: function(immediate) {
            if(!immediate && this.options.delay){
                if(!this.timer){
                    this.timer = setTimeout($.proxy(function() {
                        editTriggerPrototype.hide.apply(this, arguments);
                        this._clearTimer();
                    }, this), this.options.delay);
                }
            } else {
                editTriggerPrototype.hide.apply(this, arguments);
            }
        },
        /**
         * Clear timer
         * @protected
         */
        _clearTimer: function() {
            if (this.timer) {
                clearTimeout(this.timer);
                this.timer = null;
            }
        }
    }));
}));