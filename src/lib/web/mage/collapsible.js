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
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    var hideProps = {},
        showProps = {};

    hideProps.height =  "hide";
    showProps.height =  "show";

    $.widget("mage.collapsible", {
        options: {
            active: false,
            disabled: false,
            collapsible: true,
            header: "[data-role=title]",
            content: "[data-role=content]",
            trigger: "[data-role=trigger]",
            closedState: null,
            openedState: null,
            disabledState: null,
            ajaxUrlElement: "[data-ajax=true]",
            ajaxContent: false,
            loadingClass: null,
            saveState: false,
            animate: false,
            icons: {
                activeHeader: null,
                header: null
            },
            collateral: {
                element: null,
                openedState: null
            }
        },

        _create: function () {
            this.storage= $.localStorage;
            this.icons = false;
            if((typeof this.options.icons) === "string") {
                this.options.icons = $.parseJSON(this.options.icons);
            }

            this._processPanels();
            this._processState();
            this._refresh();


            if (this.options.icons.header && this.options.icons.activeHeader) {
                this._createIcons();
                this.icons = true;
            }

            this._bind("click");
        },

        _refresh: function () {
            this.trigger.attr("tabIndex",0);
            if (this.options.active && !this.options.disabled) {
                if (this.options.openedState) {
                    this.element.addClass(this.options.openedState);
                }
                if (this.options.collateral.element && this.options.collateral.openedState) {
                    $(this.options.collateral.element).addClass(this.options.collateral.openedState);
                }
                if (this.options.ajaxContent) {
                    this._loadContent();
                }
            }
            else if (this.options.disabled) {
                this.disable();
            } else {
                this.content.hide();
                if(this.options.closedState) {
                    this.element.addClass(this.options.closedState);
                }
            }
        },

        /**
         * Processing the state:
         *     If deep linking is used and the anchor is the id of the content or the content contains this id,
         *     and the collapsible element is a nested one having collapsible parents, in order to see the content,
         *     all the parents must be expanded.
         * @private
         */
        _processState: function () {
            var anchor = window.location.hash;
            var urlPath = window.location.pathname;
            this.stateKey = encodeURIComponent(urlPath + this.element.attr("id"));
            if ( anchor && ( $(this.content.find(anchor)).length > 0 || this.content.attr("id") === anchor.replace("#","")) ) {
                this.element.parents("[data-collapsible=true]").collapsible("forceActivate");
                if(!this.options.disabled) {
                    this.options.active = true;
                    if (this.options.saveState) {
                        this.storage.set(this.stateKey,true);
                    }
                }
            } else if (this.options.saveState && !this.options.disabled) {
                var state = this.storage.get(this.stateKey);
                if (typeof state === 'undefined' || state === null) {
                    this.storage.set(this.stateKey,this.options.active);
                } else if (state === "true") {
                    this.options.active = true;
                } else if (state === "false") {
                    this.options.active = false;
                }
            }
        },

        _createIcons: function () {
            var icons = this.options.icons;
            if (icons) {
                $("<span>")
                    .addClass(icons.header)
                    .attr("data-role","icons")
                    .prependTo(this.header);
                if (this.options.active && !this.options.disabled) {
                    this.header.children("[data-role=icons]")
                        .removeClass(icons.header)
                        .addClass(icons.activeHeader);
                }
            }
        },

        _destroyIcons: function () {
            this.header
                .children("[data-role=icons]")
                .remove();
        },

        _destroy: function () {
            var options = this.options;

            this.element.removeAttr("data-collapsible");

            this.trigger.removeAttr("tabIndex");
            if(options.openedState) {
                this.element.removeClass(options.openedState);
            }
            if (this.options.collateral.element && this.options.collateral.openedState) {
                $(this.options.collateral.element).removeClass(this.options.collateral.openedState);
            }
            if(options.closedState){
                this.element.removeClass(options.closedState);
            }
            if(options.disabledState){
                this.element.removeClass(options.disabledState);
            }

            if (this.icons) {
                this._destroyIcons();
            }
        },

        _processPanels: function () {
            this.element.attr("data-collapsible", "true");

            if (typeof this.options.header === "object") {
                this.header = this.options.header;
            } else {
                var headers = this.element.find(this.options.header);
                if(headers.length > 0) {
                    this.header = headers.eq(0);
                } else {
                    this.header = this.element;
                }
            }

            if (typeof this.options.content === "object") {
                this.content = this.options.content;
            } else {
                this.content = this.header.next(this.options.content).eq(0);
            }

            if (typeof this.options.trigger === "object") {
                this.trigger = this.options.trigger;
            } else {
                var triggers = this.header.find(this.options.trigger);
                if(triggers.length > 0) {
                    this.trigger = triggers.eq(0);
                } else {
                    this.trigger = this.header;
                }
            }
        },

        _keydown: function (event) {
            if (event.altKey || event.ctrlKey) {
                return;
            }

            var keyCode = $.ui.keyCode;

            switch (event.keyCode) {
                case keyCode.SPACE:
                case keyCode.ENTER:
                    this._eventHandler(event);
                    break;
            }

        },

        _bind: function (event) {
            this.events = {
                keydown: "_keydown"
            };
            var self = this;
            if (event) {
                $.each(event.split(" "), function (index, eventName) {
                    self.events[ eventName ] = "_eventHandler";
                });
            }
            this._off(this.trigger);
            if(!this.options.disabled) {
                this._on(this.trigger, this.events);
            }
        },

        disable: function() {
            this._off(this.trigger);
            this.forceDeactivate();
            this.options.disabled = true;
            if(this.options.disabledState) {
                this.element.addClass(this.options.disabledState);
            }
            this.trigger.attr("tabIndex",-1);
        },

        enable: function() {
            this._on(this.trigger, this.events);
            this.options.disabled = false;
            if(this.options.disabledState) {
                this.element.removeClass(this.options.disabledState);
            }
        },

        _eventHandler: function (event) {

            if (this.options.active && this.options.collapsible) {
                this.deactivate();
            } else {
                this.activate();

            }
            event.preventDefault();

        },

        _animate: function(prop) {
            var duration,
                easing,
                animate = this.options.animate;

            if ( typeof animate === "number" ) {
                duration = animate;
            }
            if (typeof animate === "string" ) {
                animate = $.parseJSON(animate);
            }
            duration = duration || animate.duration;
            easing = animate.easing;
            this.content.animate(prop,duration,easing);
        },

        deactivate: function () {
            if(this.options.animate) {
                this._animate(hideProps);
            } else {
                this.content.hide();
            }
            this._close();
        },

        forceDeactivate: function () {
            this.content.hide();
            this._close();

        },

        _close: function () {

            this.options.active = false;

            if (this.options.saveState) {
                this.storage.set(this.stateKey,false);
            }
            if (this.options.openedState) {
                this.element.removeClass(this.options.openedState);
            }
            if (this.options.collateral.element && this.options.collateral.openedState) {
                $(this.options.collateral.element).removeClass(this.options.collateral.openedState);
            }
            if(this.options.closedState) {
                this.element.addClass(this.options.closedState);
            }
            if (this.icons) {
                this.header.children("[data-role=icons]")
                    .removeClass(this.options.icons.activeHeader)
                    .addClass(this.options.icons.header);
            }

            this.element.trigger('dimensionsChanged');
        },

        activate: function () {
            if (!this.options.disabled) {
                if (this.options.animate) {
                    this._animate(showProps);
                } else {
                    this.content.show();
                }
                this._open();
            }
        },

        forceActivate: function () {
            if (!this.options.disabled) {
                this.content.show();
                this._open();
            }
        },

        _open: function () {

            this.element.trigger("beforeOpen");

            this.options.active = true;

            if (this.options.ajaxContent) {
                this._loadContent();
            }
            if (this.options.saveState) {
                this.storage.set(this.stateKey,true);
            }
            if (this.options.openedState) {
                this.element.addClass(this.options.openedState);
            }
            if (this.options.collateral.element && this.options.collateral.openedState) {
                $(this.options.collateral.element).addClass(this.options.collateral.openedState);
            }
            if (this.options.closedState) {
                this.element.removeClass(this.options.closedState);
            }
            if (this.icons) {
                this.header.children("[data-role=icons]")
                    .removeClass(this.options.icons.header)
                    .addClass(this.options.icons.activeHeader);
            }

            this.element.trigger('dimensionsChanged');
        },

        _loadContent: function () {
            var url = this.element.find(this.options.ajaxUrlElement).attr("href");
            if(url) {
                this.xhr = $.get(url, function () {
                });
            }
            var that = this;
            if (that.xhr && that.xhr.statusText !== "canceled") {
                if(that.options.loadingClass) {
                    that.element.addClass(that.options.loadingClass);
                }
                that.content.attr("aria-busy", "true");
                that.xhr
                    .success(function (response) {
                        setTimeout(function () {
                            that.content.html(response);
                        }, 1);
                    })
                    .complete(function (jqXHR, status) {
                        setTimeout(function () {
                            if (status === "abort") {
                                that.content.stop(false, true);
                            }
                            if(that.options.loadingClass) {
                                that.element.removeClass(that.options.loadingClass);
                            }
                            that.content.removeAttr("aria-busy");
                            if (jqXHR === that.xhr) {
                                delete that.xhr;
                            }
                        }, 1);
                    });
            }
        }

    });
});
