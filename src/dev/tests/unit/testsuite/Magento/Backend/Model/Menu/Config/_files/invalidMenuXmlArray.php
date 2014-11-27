<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    'add_action_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><add action="" id="Test_Value::some_value"' .
        ' title="Notifications" module="Test_Value"' .
        ' resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'action': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[a-zA-Z0-9/_]{3,}'.",
            "Element 'add', attribute 'action': '' is not a valid value of the atomic type 'typeAction'."
        )
    ),
    'add_action_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add action="ad" id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'action': [facet 'pattern'] The value 'ad' is not accepted by the " .
            "pattern '[a-zA-Z0-9/_]{3,}'.",
            "Element 'add', attribute 'action': 'ad' is not a valid value of the atomic type 'typeAction'."
        )
    ),
    'add_action_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><add action="adm$#@inhtml/notification"' .
        ' id="Test_Value::some_value" title="Notifications"' .
        ' module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'action': [facet 'pattern'] The value 'adm$#@inhtml/notification' is not " .
            "accepted by the pattern '[a-zA-Z0-9/_]{3,}'.",
            "Element 'add', attribute 'action': 'adm$#@inhtml/notification' is not a valid value of the atomic " .
            "type 'typeAction'."
        )
    ),
    'add_dependsOnConfig_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><add dependsOnConfig="" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'dependsOnConfig': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9_/]{3,}'.",
            "Element 'add', attribute 'dependsOnConfig': '' ".
            "is not a valid value of the atomic type 'typeDependsConfig'."
        )
    ),
    'add_dependsOnConfig_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add dependsOnConfig="v" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'dependsOnConfig': [facet 'pattern'] The value 'v' is not accepted by the " .
            "pattern '[A-Za-z0-9_/]{3,}'.",
            "Element 'add', attribute 'dependsOnConfig': 'v' is not a valid value of the atomic " .
            "type 'typeDependsConfig'."
        )
    ),
    'add_dependsOnConfig_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><add dependsOnConfig="name#1" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'dependsOnConfig': [facet 'pattern'] The value 'name#1' is not accepted by " .
            "the pattern '[A-Za-z0-9_/]{3,}'.",
            "Element 'add', attribute 'dependsOnConfig': 'name#1' is not a valid value of the atomic " .
            "type 'typeDependsConfig'."
        )
    ),
    'add_dependsOnModule_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><add dependsOnModule="" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'dependsOnModule': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'add', attribute 'dependsOnModule': '' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'add_dependsOnModule_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add dependsOnModule="w" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'dependsOnModule': [facet 'pattern'] The value 'w' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'add', attribute 'dependsOnModule': 'w' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'add_dependsOnModule_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><add dependsOnModule="@#erw" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'dependsOnModule': [facet 'pattern'] The value '@#erw' is not " .
            "accepted by the pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'add', attribute 'dependsOnModule': '@#erw' is not a valid value of the atomic " .
            "type 'typeModule'."
        )
    ),
    'add_id_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><add id="" title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'id': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'add', attribute 'id': '' is not a valid value of the atomic type 'typeId'.",
            "Element 'add', attribute 'id': Warning: No precomputed value available, the value was either invalid or " .
            "something strange happend."
        )
    ),
    'add_id_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add id="ma" title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'id': [facet 'pattern'] The value 'ma' is not accepted by the " .
            "pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'add', attribute 'id': 'ma' is not a valid value of the atomic type 'typeId'.",
            "Element 'add', attribute 'id': Warning: No precomputed value available, the value was either invalid or " .
            "something strange happend."
        )
    ),
    'add_id_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><add id="Magento)value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'id': [facet 'pattern'] The value 'Magento)value::some_value' is not " .
            "accepted by the pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'add', attribute 'id': 'Magento)value::some_value' ".
            "is not a valid value of the atomic type 'typeId'.",
            "Element 'add', attribute 'id': Warning: No precomputed value available, the value was either invalid or " .
            "something strange happend."
        )
    ),
    'add_module_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><add module="" id="Test_Value::some_value" ' .
        'title="Notifications" resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'module': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'add', attribute 'module': '' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'add_module_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add module="we" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'module': [facet 'pattern'] The value 'we' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'add', attribute 'module': 'we' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'add_module_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><add module="Test_Va%lue" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'module': [facet 'pattern'] The value 'Test_Va%lue' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'add', attribute 'module': 'Test_Va%lue' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'add_parent_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><add parent="" id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'parent': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'add', attribute 'parent': '' is not a valid value of the atomic type 'typeId'."
        )
    ),
    'add_parent_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add parent="Ma" id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'parent': [facet 'pattern'] The value 'Ma' is not accepted by the " .
            "pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'add', attribute 'parent': 'Ma' is not a valid value of the atomic type 'typeId'."
        )
    ),
    'add_parent_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><add parent="Some#Name::system_other_settings" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'parent': [facet 'pattern'] The value 'Some#Name::system_other_settings' " .
            "is not accepted by the pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'add', attribute 'parent': 'Some#Name::system_other_settings' ".
            "is not a valid value of the atomic " .
            "type 'typeId'."
        )
    ),
    'add_resource_attribute_notvalid_regexp_value1' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'resource': [facet 'pattern'] The value 'test_Value::value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'add', attribute 'resource': 'test_Value::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'add_resource_attribute_notvalid_regexp_value2' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'resource': [facet 'pattern'] The value 'Test_value::value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'add', attribute 'resource': 'Test_value::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'add_resource_attribute_notvalid_regexp_value3' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="M#$%23_value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'resource': [facet 'pattern'] The value 'M#$%23_value::value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'add', attribute 'resource': 'M#$%23_value::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'add_resource_attribute_notvalid_regexp_value4' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="_value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'resource': [facet 'pattern'] The value '_value::value' is not accepted by " .
            "the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'add', attribute 'resource': '_value::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'add_resource_attribute_notvalid_regexp_value5' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" resource="Magento_::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'resource': [facet 'pattern'] The value 'Magento_::value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'add', attribute 'resource': 'Magento_::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'add_resource_attribute_notvalid_regexp_value6' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value:value"/></menu></config>',
        array(
            "Element 'add', attribute 'resource': [facet 'pattern'] The value 'Test_Value:value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'add', attribute 'resource': 'Test_Value:value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'add_resource_attribute_notvalid_regexp_value7' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::"/></menu></config>',
        array(
            "Element 'add', attribute 'resource': [facet 'pattern'] The value 'Test_Value::' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'add', attribute 'resource': 'Test_Value::' ".
            "is not a valid value of the atomic type 'typeResource'."
        )
    ),
    'add_sortOrder_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><add sortOrder="" id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array("Element 'add', attribute 'sortOrder': '' is not a valid value of the atomic type 'xs:int'.")
    ),
    'add_sortOrder_attribute_wrong_value_type' => array(
        '<?xml version="1.0"?><config><menu><add sortOrder="string value" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'sortOrder': 'string value' is not a valid value of the atomic " .
            "type 'xs:int'."
        )
    ),
    'add_title_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><add title="" id="Test_Value::some_value" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'title': [facet 'minLength'] The value '' has a length of '0'; this " .
            "underruns the allowed minimum length of '3'.",
            "Element 'add', attribute 'title': '' is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'add_title_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add title="No" id="Test_Value::some_value" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'title': [facet 'minLength'] The value 'No' has a length of '2'; this " .
            "underruns the allowed minimum length of '3'.",
            "Element 'add', attribute 'title': 'No' is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'add_title_attribute_more_maxLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add title="Lorem ipsum dolor sit amet, consectetur adipisicing" ' .
        'id="Test_Value::some_value" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'title': [facet 'maxLength'] The value 'Lorem ipsum dolor sit amet, " .
            "consectetur adipisicing' has a length of '51'; this exceeds the allowed maximum length of '50'.",
            "Element 'add', attribute 'title': 'Lorem ipsum dolor sit amet, consectetur adipisicing' is not a " .
            "valid value of the atomic type 'typeTitle'."
        )
    ),
    'add_toolTip_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><add toolTip="" id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'toolTip': [facet 'minLength'] The value '' has a length of '0'; this " .
            "underruns the allowed minimum length of '3'.",
            "Element 'add', attribute 'toolTip': '' is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'add_toolTip_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add toolTip="st" id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'add', attribute 'toolTip': [facet 'minLength'] The value 'st' has a length of '2'; this " .
            "underruns the allowed minimum length of '3'.",
            "Element 'add', attribute 'toolTip': 'st' is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'add_toolTip_attribute_more_maxLenght_value' => array(
        '<?xml version="1.0"?><config><menu><add toolTip="Lorem ipsum dolor sit amet, consectetur adipisicing" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add', attribute 'toolTip': [facet 'maxLength'] The value 'Lorem ipsum dolor sit amet, " .
            "consectetur adipisicing' has a length of '51'; this exceeds the allowed maximum length of '50'.",
            "Element 'add', attribute 'toolTip': 'Lorem ipsum dolor sit amet, consectetur adipisicing' is not a " .
            "valid value of the atomic type 'typeTitle'."
        )
    ),
    'add_with_notallowed_atrribute' => array(
        '<?xml version="1.0"?><config><menu><add notallowed="some value" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array("Element 'add', attribute 'notallowed': The attribute 'notallowed' is not allowed.")
    ),
    'add_with_same_id_attribute_value' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value" ' .
        'resource="Test_Value::value"/> ' .
        '<add id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" sortOrder="10" parent="Test_Value::system_other_settings" ' .
        'action="adminhtml/notification" resource="Test_Value::value"/>' .
        '</menu></config>',
        array(
            "Element 'add': Duplicate key-sequence ['Test_Value::some_value'] in unique " .
            "identity-constraint 'uniqueAddItemId'."
        )
    ),
    'add_without_req_attr' => array(
        '<?xml version="1.0"?><config><menu><add action="adminhtml/notification"/></menu></config>',
        array(
            "Element 'add': The attribute 'id' is required but missing.",
            "Element 'add': The attribute 'title' is required but missing.",
            "Element 'add': The attribute 'module' is required but missing.",
            "Element 'add': The attribute 'resource' is required but missing."
        )
    ),
    'add_without_required_attribute_id' => array(
        '<?xml version="1.0"?><config><menu><add title="Notifications" module="Test_Value" ' .
        'sortOrder="10" parent="Test_Value::system_other_settings" action="adminhtml/notification" ' .
        'resource="Test_Value::value"/></menu></config>',
        array("Element 'add': The attribute 'id' is required but missing.")
    ),
    'add_without_required_attribute_module' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" resource="Test_Value::value"/></menu></config>',
        array("Element 'add': The attribute 'module' is required but missing.")
    ),
    'add_without_required_attribute_resource' => array(
        '<?xml version="1.0"?><config><menu><add id="Test_Value::some_value" ' .
        'title="Notifications" module="Test_Value"/></menu></config>',
        array("Element 'add': The attribute 'resource' is required but missing.")
    ),
    'double_menu' => array(
        '<?xml version="1.0"?><config><menu></menu><menu/></config>',
        array("Element 'menu': This element is not expected.")
    ),
    'remove_id_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><remove id=""/></menu></config>',
        array(
            "Element 'remove', attribute 'id': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'remove', attribute 'id': '' is not a valid value of the atomic type 'typeId'."
        )
    ),
    'remove_id_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><remove id="Test_Value::system_%currency"/></menu></config>',
        array(
            "Element 'remove', attribute 'id': [facet 'pattern'] The value 'Test_Value::system_%currency' is not " .
            "accepted by the pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'remove', attribute 'id': 'Test_Value::system_%currency' is not a valid value of the " .
            "atomic type 'typeId'."
        )
    ),
    'remove_id_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><remove id="Test_Value::system#currency"/></menu></config>',
        array(
            "Element 'remove', attribute 'id': [facet 'pattern'] The value 'Test_Value::system#currency' is not " .
            "accepted by the pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'remove', attribute 'id': 'Test_Value::system#currency' is not a valid value of the " .
            "atomic type 'typeId'."
        )
    ),
    'remove_with_notallowed_atrribute' => array(
        '<?xml version="1.0"?><config><menu><remove id="Test_Value::system_currency" notallowe="some text"/>' .
        '</menu></config>',
        array("Element 'remove', attribute 'notallowe': The attribute 'notallowe' is not allowed.")
    ),
    'remove_without_required_attribute_id' => array(
        '<?xml version="1.0"?><config><menu><remove/></menu></config>',
        array("Element 'remove': The attribute 'id' is required but missing.")
    ),
    'update_action_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><update action="" ' . 'id="Test_Value::some_value"/></menu></config>',
        array(
            "Element 'update', attribute 'action': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[a-zA-Z0-9/_]{3,}'.",
            "Element 'update', attribute 'action': '' is not a valid value of the atomic type 'typeAction'."
        )
    ),
    'update_action_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update action="v" ' .
        'id="Test_Value::some_value" ' .
        'resource="Test_Value::value"/></menu></config>',
        array(
            "Element 'update', attribute 'action': [facet 'pattern'] The value 'v' is not accepted by the " .
            "pattern '[a-zA-Z0-9/_]{3,}'.",
            "Element 'update', attribute 'action': 'v' is not a valid value of the atomic type 'typeAction'."
        )
    ),
    'update_action_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><update action="/@##gt;" ' .
        'id="Test_Value::some_value"/></menu></config>',
        array(
            "Element 'update', attribute 'action': [facet 'pattern'] The value '/@##gt;' is not " .
            "accepted by the pattern '[a-zA-Z0-9/_]{3,}'.",
            "Element 'update', attribute 'action': '/@##gt;' is not a valid value of the atomic type 'typeAction'."
        )
    ),
    'update_dependsOnConfig_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" dependsOnConfig=""/></menu>' .
        '</config>',
        array(
            "Element 'update', attribute 'dependsOnConfig': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9_/]{3,}'.",
            "Element 'update', attribute 'dependsOnConfig': '' is not a valid value of the atomic " .
            "type 'typeDependsConfig'."
        )
    ),
    'update_dependsOnConfig_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'dependsOnConfig="we"/></menu></config>',
        array(
            "Element 'update', attribute 'dependsOnConfig': [facet 'pattern'] The value 'we' is not accepted by " .
            "the pattern '[A-Za-z0-9_/]{3,}'.",
            "Element 'update', attribute 'dependsOnConfig': 'we' is not a valid value of the atomic " .
            "type 'typeDependsConfig'."
        )
    ),
    'update_dependsOnConfig_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" dependsOnConfig="someconf%"/>' .
        '</menu></config>',
        array(
            "Element 'update', attribute 'dependsOnConfig': [facet 'pattern'] The value 'someconf%' is not " .
            "accepted by the pattern '[A-Za-z0-9_/]{3,}'.",
            "Element 'update', attribute 'dependsOnConfig': 'someconf%' is not a valid value of the atomic " .
            "type 'typeDependsConfig'."
        )
    ),
    'update_dependsOnModule_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'dependsOnModule=""/></menu></config>',
        array(
            "Element 'update', attribute 'dependsOnModule': [facet 'pattern'] The value '' is not accepted by " .
            "the pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'update', attribute 'dependsOnModule': '' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'update_dependsOnModule_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'dependsOnModule="qw"/></menu></config>',
        array(
            "Element 'update', attribute 'dependsOnModule': [facet 'pattern'] The value 'qw' is not accepted " .
            "by the pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'update', attribute 'dependsOnModule': 'qw' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'update_dependsOnModule_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'dependsOnModule="someModule#1"/></menu></config>',
        array(
            "Element 'update', attribute 'dependsOnModule': [facet 'pattern'] The value 'someModule#1' is not " .
            "accepted by the pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'update', attribute 'dependsOnModule': 'someModule#1' is not a valid value of the atomic " .
            "type 'typeModule'."
        )
    ),
    'update_id_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><update id="" title="Notifications"/></menu></config>',
        array(
            "Element 'update', attribute 'id': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'update', attribute 'id': '' is not a valid value of the atomic type 'typeId'."
        )
    ),
    'update_id_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update id="g" module="Test_Value"/></menu></config>',
        array(
            "Element 'update', attribute 'id': [facet 'pattern'] The value 'g' is not accepted by the " .
            "pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'update', attribute 'id': 'g' is not a valid value of the atomic type 'typeId'."
        )
    ),
    'update_id_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Magento+value::some_value"/>' . '</menu></config>',
        array(
            "Element 'update', attribute 'id': [facet 'pattern'] The value 'Magento+value::some_value' is not " .
            "accepted by the pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'update', attribute 'id': 'Magento+value::some_value' is not a valid value of the atomic " .
            "type 'typeId'."
        )
    ),
    'update_module_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><update module="" id="Module_Name::system_config"/></menu></config>',
        array(
            "Element 'update', attribute 'module': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'update', attribute 'module': '' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'update_module_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" module="we"/></menu></config>',
        array(
            "Element 'update', attribute 'module': [facet 'pattern'] The value 'we' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'update', attribute 'module': 'we' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'update_module_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" module="@#$"/></menu></config>',
        array(
            "Element 'update', attribute 'module': [facet 'pattern'] The value '@#$' is not accepted by the " .
            "pattern '[A-Za-z0-9_]{3,}'.",
            "Element 'update', attribute 'module': '@#$' is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'update_parent_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><update parent="" ' . 'id="Test_Value::some_value"/></menu></config>',
        array(
            "Element 'update', attribute 'parent': [facet 'pattern'] The value '' is not accepted by the " .
            "pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'update', attribute 'parent': '' is not a valid value of the atomic type 'typeId'."
        )
    ),
    'update_parent_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update parent="fg" ' . 'id="Test_Value::some_value"/></menu></config>',
        array(
            "Element 'update', attribute 'parent': [facet 'pattern'] The value 'fg' is not accepted by " .
            "the pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'update', attribute 'parent': 'fg' is not a valid value of the atomic type 'typeId'."
        )
    ),
    'update_parent_attribute_notallowed_symbols_value' => array(
        '<?xml version="1.0"?><config><menu><update parent="Test_Value::system_other%settings" ' .
        'id="Test_Value::some_value"/></menu></config>',
        array(
            "Element 'update', attribute 'parent': [facet 'pattern'] The value " .
            "'Test_Value::system_other%settings' is not accepted by the pattern '[A-Za-z0-9/_:]{3,}'.",
            "Element 'update', attribute 'parent': 'Test_Value::system_other%settings' is not a valid value of the " .
            "atomic type 'typeId'."
        )
    ),
    'update_resource_attribute_notvalid_regexp_value1' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'resource="test_Value::value"/></menu></config>',
        array(
            "Element 'update', attribute 'resource': [facet 'pattern'] The value 'test_Value::value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'update', attribute 'resource': 'test_Value::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'update_resource_attribute_notvalid_regexp_value2' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'resource="Test_value::value"/></menu></config>',
        array(
            "Element 'update', attribute 'resource': [facet 'pattern'] The value 'Test_value::value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'update', attribute 'resource': 'Test_value::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'update_resource_attribute_notvalid_regexp_value3' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'resource="M#$%23_value::value"/></menu></config>',
        array(
            "Element 'update', attribute 'resource': [facet 'pattern'] The value 'M#$%23_value::value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'update', attribute 'resource': 'M#$%23_value::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'update_resource_attribute_notvalid_regexp_value4' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'resource="_value::value"/></menu></config>',
        array(
            "Element 'update', attribute 'resource': [facet 'pattern'] The value '_value::value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'update', attribute 'resource': '_value::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'update_resource_attribute_notvalid_regexp_value5' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'resource="Magento_::value"/></menu></config>',
        array(
            "Element 'update', attribute 'resource': [facet 'pattern'] The value 'Magento_::value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'update', attribute 'resource': 'Magento_::value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'update_resource_attribute_notvalid_regexp_value6' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'resource="Test_Value:value"/></menu></config>',
        array(
            "Element 'update', attribute 'resource': [facet 'pattern'] The value 'Test_Value:value' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'update', attribute 'resource': 'Test_Value:value' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'update_resource_attribute_notvalid_regexp_value7' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'resource="Test_Value::"/></menu></config>',
        array(
            "Element 'update', attribute 'resource': [facet 'pattern'] The value 'Test_Value::' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'update', attribute 'resource': 'Test_Value::' is not a valid value of the atomic " .
            "type 'typeResource'."
        )
    ),
    'update_sortOrder_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><update sortOrder="" ' . 'id="Test_Value::some_value"/></menu></config>',
        array("Element 'update', attribute 'sortOrder': '' is not a valid value of the atomic type 'xs:int'.")
    ),
    'update_sortOrder_attribute_wrong_value_type' => array(
        '<?xml version="1.0"?><config><menu><add sortOrder="string" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" resource="Test_Value::value"/>' .
        '</menu></config>',
        array("Element 'add', attribute 'sortOrder': 'string' is not a valid value of the atomic type 'xs:int'.")
    ),
    'update_title_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" title=""/></menu></config>',
        array(
            "Element 'update', attribute 'title': [facet 'minLength'] The value '' has a length of '0'; this " .
            "underruns the allowed minimum length of '3'.",
            "Element 'update', attribute 'title': '' is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'update_title_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" title="am"/></menu></config>',
        array(
            "Element 'update', attribute 'title': [facet 'minLength'] The value 'am' has a length of '2'; this " .
            "underruns the allowed minimum length of '3'.",
            "Element 'update', attribute 'title': 'am' is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'update_title_attribute_more_maxLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'title="Lorem ipsum dolor sit amet, consectetur adipisicing"/></menu></config>',
        array(
            "Element 'update', attribute 'title': [facet 'maxLength'] The value 'Lorem ipsum dolor sit amet, " .
            "consectetur adipisicing' has a length of '51'; this exceeds the allowed maximum length of '50'.",
            "Element 'update', attribute 'title': 'Lorem ipsum dolor sit amet, ".
            "consectetur adipisicing' is not a valid " .
            "value of the atomic type 'typeTitle'."
        )
    ),
    'update_toolTip_attribute_empty_value ' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" toolTip=""/></menu></config>',
        array(
            "Element 'update', attribute 'toolTip': [facet 'minLength'] The value '' has a length of '0'; this " .
            "underruns the allowed minimum length of '3'.",
            "Element 'update', attribute 'toolTip': '' is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'update_toolTip_attribute_less_minLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" toolTip="we"/></menu></config>',
        array(
            "Element 'update', attribute 'toolTip': [facet 'minLength'] The value 'we' has a length of '2'; this " .
            "underruns the allowed minimum length of '3'.",
            "Element 'update', attribute 'toolTip': 'we' is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'update_toolTip_attribute_more_maxLenght_value' => array(
        '<?xml version="1.0"?><config><menu><update id="Module_Name::system_config" ' .
        'toolTip="Lorem ipsum dolor sit amet, consectetur adipisicing"/></menu></config>',
        array(
            "Element 'update', attribute 'toolTip': [facet 'maxLength'] The value 'Lorem ipsum dolor sit " .
            "amet, consectetur adipisicing' has a length of '51'; this exceeds the allowed maximum length of '50'.",
            "Element 'update', attribute 'toolTip': 'Lorem ipsum dolor sit amet, consectetur adipisicing' " .
            "is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'update_with_notallowed_atrribute' => array(
        '<?xml version="1.0"?><config><menu><update notallowed="some value" ' .
        'id="Test_Value::some_value" title="Notifications" ' .
        'module="Test_Value" sortOrder="10" parent="Test_Value::system_other_settings" ' .
        'action="adminhtml/notification" resource="Test_Value::value"/>' .
        '</menu></config>',
        array("Element 'update', attribute 'notallowed': The attribute 'notallowed' is not allowed.")
    ),
    'update_without_required_attribute_id' => array(
        '<?xml version="1.0"?><config><menu><update title="some text"/></menu></config>',
        array("Element 'update': The attribute 'id' is required but missing.")
    ),
    'without_menu' => array(
        '<?xml version="1.0"?><config></config>',
        array("Element 'config': Missing child element(s). Expected is ( menu ).")
    )
);
