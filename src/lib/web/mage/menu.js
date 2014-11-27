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
 /*global mediaCheck*/
define([
    "jquery",
    "jquery/ui",
    "jquery/jquery.mobile.custom"
], function($){
    'use strict';

    /**
     * Menu Widget - this widget is a wrapper for the jQuery UI Menu
     */
    $.widget('mage.menu', $.ui.menu, {
        options: {
            responsive: false,
            expanded: false,
            delay: 300
        },

        _init: function() {
            this._super();
            this.delay = this.options.delay;

            if(this.options.expanded === true) {
                this.isExpanded();
            }

            if(this.options.responsive === true){
                mediaCheck({
                    media: '(max-width: 640px)',
                    entry: $.proxy(function() {
                        this._toggleMobileMode();
                    }, this),
                    exit: $.proxy(function() {
                        this._toggleDesktopMode();
                    }, this)
                });
            }

            this._assignControls()._listen();
        },

        _assignControls: function() {
            this.controls = {
                toggleBtn: $('[data-action="toggle-nav"]'),
                swipeArea: $('.nav-sections')
            };

            return this;
        },

        _listen: function() {
            var controls = this.controls;
            var toggle = this.toggle;

            this._on(controls.toggleBtn, { 'click'    : toggle });
            this._on(controls.swipeArea, { 'swipeleft': toggle });
        },

        toggle: function() {
            if ($('html').hasClass('nav-open')) {
                $('html').removeClass('nav-open');
                setTimeout(function() {
                    $('html').removeClass('nav-before-open');
                },300);
            } else {
                $('html').addClass('nav-before-open');
                setTimeout(function() {
                    $('html').addClass('nav-open');
                },42);
            }
        },

        //Add class for expanded option
        isExpanded: function() {
            var subMenus = this.element.find( this.options.menus ),
                expandedMenus = subMenus.find('ul');

            expandedMenus.addClass('expanded');
        },

        _activate: function( event ) {
            window.location.href = this.active.find('> a').attr('href');
            this.collapseAll(event);
        },

        _keydown: function(event) {

            var match, prev, character, skip, regex,
            preventDefault = true;

            function escape( value ) {
                return value.replace( /[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&" );
            }

            if(this.active.closest('ul').attr('aria-expanded') != 'true') {

                switch ( event.keyCode ) {
                    case $.ui.keyCode.PAGE_UP:
                        this.previousPage( event );
                        break;
                    case $.ui.keyCode.PAGE_DOWN:
                        this.nextPage( event );
                        break;
                    case $.ui.keyCode.HOME:
                        this._move( "first", "first", event );
                        break;
                    case $.ui.keyCode.END:
                        this._move( "last", "last", event );
                        break;
                    case $.ui.keyCode.UP:
                        this.previous( event );
                        break;
                    case $.ui.keyCode.DOWN:
                        if ( this.active && !this.active.is( ".ui-state-disabled" ) ) {
                            this.expand( event );
                        }
                        break;
                    case $.ui.keyCode.LEFT:
                        this.previous( event );
                        break;
                    case $.ui.keyCode.RIGHT:
                        this.next( event );
                        break;
                    case $.ui.keyCode.ENTER:
                    case $.ui.keyCode.SPACE:
                        this._activate( event );
                        break;
                    case $.ui.keyCode.ESCAPE:
                        this.collapse( event );
                        break;
                    default:
                        preventDefault = false;
                        prev = this.previousFilter || "";
                        character = String.fromCharCode( event.keyCode );
                        skip = false;

                        clearTimeout( this.filterTimer );

                        if ( character === prev ) {
                            skip = true;
                        } else {
                            character = prev + character;
                        }

                        regex = new RegExp( "^" + escape( character ), "i" );
                        match = this.activeMenu.children( ".ui-menu-item" ).filter(function() {
                            return regex.test( $( this ).children( "a" ).text() );
                        });
                        match = skip && match.index( this.active.next() ) !== -1 ?
                            this.active.nextAll( ".ui-menu-item" ) :
                            match;

                        // If no matches on the current filter, reset to the last character pressed
                        // to move down the menu to the first item that starts with that character
                        if ( !match.length ) {
                            character = String.fromCharCode( event.keyCode );
                            regex = new RegExp( "^" + escape( character ), "i" );
                            match = this.activeMenu.children( ".ui-menu-item" ).filter(function() {
                                return regex.test( $( this ).children( "a" ).text() );
                            });
                        }

                        if ( match.length ) {
                            this.focus( event, match );
                            if ( match.length > 1 ) {
                                this.previousFilter = character;
                                this.filterTimer = this._delay(function() {
                                    delete this.previousFilter;
                                }, 1000 );
                            } else {
                                delete this.previousFilter;
                            }
                        } else {
                            delete this.previousFilter;
                        }
                }
            } else {
                switch ( event.keyCode ) {
                    case $.ui.keyCode.DOWN:
                        this.next( event );
                        break;
                    case $.ui.keyCode.UP:
                        this.previous( event );
                        break;
                    case $.ui.keyCode.RIGHT:
                        if ( this.active && !this.active.is( ".ui-state-disabled" ) ) {
                            this.expand( event );
                        }
                        break;
                    case $.ui.keyCode.ENTER:
                    case $.ui.keyCode.SPACE:
                        this._activate( event );
                        break;
                    case $.ui.keyCode.LEFT:
                    case $.ui.keyCode.ESCAPE:
                        this.collapse( event );
                        break;
                    default:
                        preventDefault = false;
                        prev = this.previousFilter || "";
                        character = String.fromCharCode( event.keyCode );
                        skip = false;

                        clearTimeout( this.filterTimer );

                        if ( character === prev ) {
                            skip = true;
                        } else {
                            character = prev + character;
                        }

                        regex = new RegExp( "^" + escape( character ), "i" );
                        match = this.activeMenu.children( ".ui-menu-item" ).filter(function() {
                            return regex.test( $( this ).children( "a" ).text() );
                        });
                        match = skip && match.index( this.active.next() ) !== -1 ?
                            this.active.nextAll( ".ui-menu-item" ) :
                            match;

                        // If no matches on the current filter, reset to the last character pressed
                        // to move down the menu to the first item that starts with that character
                        if ( !match.length ) {
                            character = String.fromCharCode( event.keyCode );
                            regex = new RegExp( "^" + escape( character ), "i" );
                            match = this.activeMenu.children( ".ui-menu-item" ).filter(function() {
                                return regex.test( $( this ).children( "a" ).text() );
                            });
                        }

                        if ( match.length ) {
                            this.focus( event, match );
                            if ( match.length > 1 ) {
                                this.previousFilter = character;
                                this.filterTimer = this._delay(function() {
                                    delete this.previousFilter;
                                }, 1000 );
                            } else {
                                delete this.previousFilter;
                            }
                        } else {
                            delete this.previousFilter;
                        }
                }
            }

            if ( preventDefault ) {
                event.preventDefault();
            }
        },

        _toggleMobileMode: function() {
            $(this.element).unbind('mouseenter mouseleave');
            this._on({
                "click .ui-menu-item:has(a)": function( event ) {
                    event.preventDefault();
                    var target = $( event.target ).closest( ".ui-menu-item" );
                    this.select( event );
                    if ( target.hasClass('level-top') && target.has( ".ui-menu" ).length ) {
                        this.expand( event );
                    } else {
                        window.location.href = target.find('> a').attr('href');
                    }
                }
            });

            var subMenus = this.element.find('.level-top');
            $.each(subMenus, $.proxy(function(index, item) {
                var category = $(item).find('> a span').not('.ui-menu-icon').text(),
                    categoryUrl = $(item).find('> a').attr('href'),
                    menu = $(item).find('> .ui-menu');

                this.categoryLink = $('<a>')
                    .attr('href', categoryUrl)
                    .text('All '+ category);

                this.categoryParent = $('<li>')
                    .addClass('ui-menu-item all-category')
                    .html(this.categoryLink);

                if(menu.find('.all-category').length === 0) {
                    menu.prepend(this.categoryParent);
                }

            }, this));
        },

        _toggleDesktopMode: function() {
            this._on({
                // Prevent focus from sticking to links inside menu after clicking
                // them (focus should always stay on UL during navigation).
                "mousedown .ui-menu-item > a": function( event ) {
                    event.preventDefault();
                },
                "click .ui-state-disabled > a": function( event ) {
                    event.preventDefault();
                },
                "click .ui-menu-item:has(a)": function( event ) {
                    var target = $( event.target ).closest( ".ui-menu-item" );
                    if ( !this.mouseHandled && target.not( ".ui-state-disabled" ).length ) {
                        this.select( event );

                        // Only set the mouseHandled flag if the event will bubble, see #9469.
                        if ( !event.isPropagationStopped() ) {
                            this.mouseHandled = true;
                        }

                        // Open submenu on click
                        if ( target.has( ".ui-menu" ).length ) {
                            this.expand( event );
                        } else if ( !this.element.is( ":focus" ) && $( this.document[ 0 ].activeElement ).closest( ".ui-menu" ).length ) {

                            // Redirect focus to the menu
                            this.element.trigger( "focus", [ true ] );

                            // If the active item is on the top level, let it stay active.
                            // Otherwise, blur the active item since it is no longer visible.
                            if ( this.active && this.active.parents( ".ui-menu" ).length === 1 ) {
                                clearTimeout( this.timer );
                            }
                        }
                    }
                },
                "mouseenter .ui-menu-item": function( event ) {
                    var target = $( event.currentTarget );
                    // Remove ui-state-active class from siblings of the newly focused menu item
                    // to avoid a jump caused by adjacent elements both having a class with a border
                    target.siblings().children( ".ui-state-active" ).removeClass( "ui-state-active" );
                    this.focus( event, target );
                },
                "mouseleave": function( event ){
                    this.collapseAll( event, true );
                },
                "mouseleave .ui-menu": "collapseAll"
            });

            var categoryParent = this.element.find('.all-category');

            categoryParent.remove();
        }
    });


    $.widget('mage.navigation', $.mage.menu, {

        options: {
            responsiveAction: 'wrap', //option for responsive handling
            maxItems: null, //option to set max number of menu items
            container: '#menu', //container to check against navigation length
            moreText: 'more',
            breakpoint: 768
        },

        _init: function() {
            this._super();

            var that = this,
                moreMenu = $('[responsive=more]'),
                responsive = this.options.responsiveAction;

            this.element
                .addClass('ui-menu-responsive')
                .attr('responsive', 'main');

            this.setupMoreMenu();
            this.setMaxItems();

            //check responsive option
            if(responsive == "onResize") {
                $(window).on('resize', function() {
                    if($(window).width() > that.options.breakpoint) {
                       that._responsive();
                       $('[responsive=more]').show();
                    } else {
                        that.element.children().show();
                        $('[responsive=more]').hide();
                    }
                });
            } else if(responsive == "onReload") {
                this._responsive();
            }
        },

        setupMoreMenu: function() {
            var moreListItems = this.element.children().clone(),
                moreLink = $('<a>'+ this.options.moreText +'</a>');

            moreListItems.hide();

            moreLink.attr('href', '#');

            this.moreItemsList = $('<ul>')
                .append(moreListItems);

            this.moreListContainer = $('<li>')
                .append(moreLink)
                .append(this.moreItemsList);

            this.responsiveMenu = $('<ul>')
                .addClass('ui-menu-more')
                .attr('responsive', 'more')
                .append(this.moreListContainer)
                .menu({
                    position : {
                        my : "right top",
                        at : "right bottom"
                    }
                })
                .insertAfter(this.element);
        },

        _responsive: function() {
            var container = $(this.options.container),
                containerSize = container.width(),
                width = 0,
                items = this.element.children('li'),
                more = $('.ui-menu-more > li > ul > li a');


            items = items.map(function() {
                var item = {};

                item.item = $(this);
                item.itemSize = $(this).outerWidth();
                return item;
            });

            $.each(items, function(index, item){
                var itemText = items[index].item
                        .find('a:first')
                        .text();

                width += parseInt(items[index].itemSize, null);

                if(width < containerSize) {
                    items[index].item.show();

                    more.each(function() {
                       var text = $(this).text();
                       if(text === itemText){
                           $(this).parent().hide();
                       }
                   });
                } else if(width > containerSize) {
                    items[index].item.hide();

                    more.each(function() {
                       var text = $(this).text();
                       if(text === itemText){
                           $(this).parent().show();
                       }
                   });
                }
            });
        },

        setMaxItems: function() {
            var items = this.element.children('li'),
                itemsCount = items.length,
                maxItems = this.options.maxItems,
                overflow = itemsCount - maxItems,
                overflowItems = items.slice(overflow);

            overflowItems.hide();

            overflowItems.each(function(){
                var itemText = $(this).find('a:first').text();

                $(this).hide();

                $('.ui-menu-more > li > ul > li a').each(function() {
                   var text = $(this).text();
                   if(text === itemText){
                       $(this).parent().show();
                   }
               });
            });
        }
    });

});
