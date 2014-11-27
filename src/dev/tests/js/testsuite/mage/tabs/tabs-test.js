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
TabsTest = TestCase('TabsTest');
TabsTest.prototype.testInit = function() {
    /*:DOC += <div id="tabs"></div>*/
    var tabs = jQuery('#tabs').tabs();
    assertTrue(tabs.is(':mage-tabs'));
};

TabsTest.prototype.testCreate = function() {
    /*:DOC += <div id="tabs"><ul>
        <li>
            <a href="#tab1_content" id="tab1"></a>
            <div id="tab1_content"></div>
        </li>
     <li>
        <a href="#tab2_content" id="tab2"></a>
        <div id="tab2_content"></div>
     </li>
    </ul></div>*/
    var tabs = jQuery('#tabs').tabs({active: 'tab2'});
    assertEquals(tabs.tabs('option', 'active'), tabs.data("tabs").anchors.index(jQuery('#tab2')));
};

TabsTest.prototype.testActiveAnchor = function() {
    /*:DOC += <div id="tabs"><ul>
         <li>
            <a href="#tab1_content" id="tab1"></a>
            <div id="tab1_content"></div>
         </li>
         <li>
            <a href="#tab2_content" id="tab2"></a>
            <div id="tab2_content"></div>
         </li>
     </ul></div>*/
    var tabs = jQuery('#tabs').tabs({active: 'tab2'});
    assertTrue(tabs.tabs('activeAnchor').is(tabs.data("tabs").anchors.eq(tabs.tabs('option', 'active'))));
};

TabsTest.prototype.testGetTabIndex = function() {
    /*:DOC += <div id="tabs"><ul>
         <li>
             <a href="#tab1_content" id="tab1"></a>
             <div id="tab1_content"></div>
         </li>
         <li>
             <a href="#tab2_content" id="tab2"></a>
             <div id="tab2_content"></div>
         </li>
     </ul></div>*/
    var tabs = jQuery('#tabs').tabs();
    assertEquals(0, tabs.data("tabs")._getTabIndex('tab1'));
    assertEquals(1, tabs.data("tabs")._getTabIndex('tab2'));
};

TabsTest.prototype.testGetPanelForTab = function() {
    /*:DOC += <div id="tabs"><ul>
         <li>
             <a href="#tab1_content" id="tab1"></a>
         </li>
     </ul></div>
     <div id="destination">
        <div id="tab1_content"></div>
     </div>*/
    var tabs = jQuery('#tabs').tabs({destination: '#destination'});
    assertTrue(jQuery(tabs.data("tabs")._getPanelForTab(jQuery('#tab1').closest('li'))).is('#tab1_content'));
};

TabsTest.prototype.testMovePanelsInDestination = function() {
    /*:DOC += <div id="tabs"><ul>
         <li>
            <a href="#tab1_content" id="tab1"></a>
            <div id="tab1_content"></div>
         </li>
     </ul></div>
     <div id="destination">
     </div>*/
    var tabs = jQuery('#tabs').tabs({destination: '#destination'});
    var panel = jQuery('#tab1_content');
    tabs.append(panel);
    assertTrue(panel.parents('#tabs').length > 0);
    assertEquals(panel.parents(tabs.tabs('option', 'destination')).length, 0);

    tabs.data("tabs")._movePanelsInDestination(panel);
    assertEquals(panel.parents('#tabs').length, 0);
    assertTrue(panel.parents(tabs.tabs('option', 'destination')).length > 0);

    tabs.tabs('option', 'destination', null);
    tabs.append(panel);
    assertTrue(panel.parents('#tabs').length > 0);

    tabs.data("tabs")._movePanelsInDestination(panel);
    assertTrue(panel.parents('#tabs').length > 0);
};

TabsTest.prototype.testAjaxLoad = function() {
    /*:DOC += <div id="tabs"><ul>
         <li>
             <a href="www.site.com" id="tab1">Tab 1</a>
             <div id="tab1_content"></div>
         </li>
     </ul></div>
     */
    var tabs = jQuery('#tabs').tabs(),
        ui = {
            tab: jQuery('#tab1'),
            panel: jQuery('#tab1_content')
        };
    tabs.tabs('option', 'load')({}, ui);
    assertEquals(jQuery('#tab1').attr('href'), '#tab1_content');
};

TabsTest.prototype.testOnContentChange = function() {
    /*:DOC += <div id="tabs"><ul>
         <li>
             <a href="www.site.com" id="tab1">Tab 1</a>
             <div id="tab1_content"></div>
         </li>
     </ul></div>
     */
    var eventMock = {
            data: {
                index: 0
            }
        },
        tabs = jQuery('#tabs').tabs();

    tabs.data("tabs")._onContentChange(eventMock);
    assertTrue(jQuery('#tab1').hasClass('changed'));
};

TabsTest.prototype.testOnInvalid = function() {
    /*:DOC += <div id="tabs"><ul>
         <li>
             <a href="www.site.com" id="tab1">Tab 1<span class="error">&nbsp;</span></a>
             <div id="tab1_content"></div>
         </li>
     </ul></div>
     */
    var eventMock = {
            data: {
                index: 0
            }
        },
        tabs = jQuery('#tabs').tabs(),
        errorIcon = jQuery('#tab1').find('.error');

    errorIcon.hide();
    assertTrue(errorIcon.is(':hidden'));

    tabs.data("tabs")._onInvalid(eventMock);
    assertTrue(jQuery('#tab1').hasClass('error'));
    assertTrue(errorIcon.is(':visible'));
};

TabsTest.prototype.testOnFocus = function() {
    /*:DOC += <div id="tabs"><ul>
         <li>
             <a href="#tab1_content" id="tab1"></a>
             <div id="tab1_content"></div>
         </li>
         <li>
             <a href="#tab2_content" id="tab2"></a>
             <div id="tab2_content"><input /></div>
         </li>
     </ul></div>
     */
    var eventMock = {
            data: {
                index: 1
            }
        },
        tabs = jQuery('#tabs').tabs();

    assertNotEquals(tabs.tabs('option', 'active'), eventMock.data.index);

    tabs.data("tabs")._onFocus(eventMock);
    assertEquals(tabs.tabs('option', 'active'), eventMock.data.index);
};

TabsTest.prototype.testOnBeforeSubmit = function() {
    /*:DOC += <div id="tabs"><ul>
         <li>
             <a href="#tab1_content" id="tab1"></a>
             <div id="tab1_content"></div>
         </li>
     </ul></div>
     */
    var tabs = jQuery('#tabs').tabs({active: 'tab1'}),
        data= {},
        testData = {
            action: {
                args: {
                    tab: 'tab1'
                }
            }
        },
        testDataTabArgument = {
            action: {
                args: {
                    testtab: 'tab1'
                }
            }
        },
        tabPrefix = 'test-',
        tabIdArgument = 'testtab';

    tabs.data("tabs")._onBeforeSubmit({}, data);
    assertEquals(data, testData);
    data = {};

    jQuery('#tab1').prop('id', 'test-tab1');
    tabs.tabs('option', 'tabsBlockPrefix', tabPrefix);
    tabs.data("tabs")._onBeforeSubmit({}, data);
    assertEquals(data, testData);
    tabs.tabs('option', 'tabsBlockPrefix', null);
    jQuery('#test-tab1').prop('id', 'tab1');
    data = {};

    tabs.tabs('option', 'tabIdArgument', tabIdArgument);
    tabs.data("tabs")._onBeforeSubmit({}, data);
    assertEquals(data, testDataTabArgument);
};


