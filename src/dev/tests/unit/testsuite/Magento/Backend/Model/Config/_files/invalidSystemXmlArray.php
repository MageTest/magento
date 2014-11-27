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
    'tab_id_not_unique' => array(
        '<?xml version="1.0"?><config><system><tab id="tab1"><label>Label One</label>' .
        '</tab><tab id="tab1"><label>Label Two</label></tab></system></config>',
        array("Element 'tab': Duplicate key-sequence ['tab1'] in unique identity-constraint 'uniqueTabId'.")
    ),
    'section_id_not_unique' => array(
        '<?xml version="1.0"?><config><system><section id="section1"><label>Label</label><tab>Tab</tab></section>' .
        '<section id="section1"><label>Label_One</label><tab>Tab_One</tab></section></system></config>',
        array(
            "Element 'section': Duplicate key-sequence ['section1'] " .
            "in unique identity-constraint 'uniqueSectionId'."
        )
    ),
    'field_id_not_unique' => array(
        '<?xml version="1.0"?><config><system><section id="section1"><group id="group1">' .
        '<label>Label</label><field id="field_id" /><field id="field_id" /></group>' .
        '<group id="group2"><label>Label_One</label></group></section></system></config>',
        array("Element 'field': Duplicate key-sequence ['field_id'] in unique identity-constraint 'uniqueFieldId'.")
    ),
    'field_element_id_not_expected' => array(
        '<?xml version="1.0"?><config><system><section id="section1"><label>Label</label><field id="field_id">' .
        '</field><field id="new_field_id"/></section></system></config>',
        array(
            "Element 'field': This element is not expected."
        )
    ),
    'group_id_not_unique' => array(
        '<?xml version="1.0"?><config><system><section id="section1"><group id="group1">' .
        '<label>Label</label></group>' .
        '<group id="group1"><label>Label_One</label></group></section></system></config>',
        array("Element 'group': Duplicate key-sequence ['group1'] in unique identity-constraint 'uniqueGroupId'.")
    ),
    'group_is_not_expected' => array(
        '<?xml version="1.0"?><config><system><group id="group1"><label>Label</label><tab>Tab</tab></group>' .
        '<group id="group1"><label>Label_One</label><tab>Tab_One</tab></group></system></config>',
        array("Element 'group': This element is not expected. Expected is one of ( tab, section ).")
    ),
    'upload_dir_is_not_expected' => array(
        '<?xml version="1.0"?><config><system><section id="section1"><group id="group1">' .
        '<label>Label</label><field id="field_id" /><upload_dir config="node_one/node_two/node_three" scope_info="1">' .
        'node_one/node_two/node_three</upload_dir></group>' .
        '<group id="group2"><label>Label_One</label></group></section></system></config>',
        array("Element 'upload_dir': This element is not expected.")
    ),
    'upload_dir_with_invalid_type' => array(
        '<?xml version="1.0"?><config><system><section id="section1"><group id="group1">' .
        '<label>Label</label><field id="field_id"><config_path>co</config_path>' .
        '</field></group>' .
        '<group id="group2"><label>Label_One</label></group></section></system></config>',
        array(
            "Element 'config_path': [facet 'minLength'] The value has a length of '2'; this underruns " .
            "the allowed minimum length of '5'.",
            "Element 'config_path': [facet 'pattern'] The value 'co' is not " .
            "accepted by the pattern '[a-zA-Z0-9_\\\\\\\\]+/[a-zA-Z0-9_\\\\\\\\]+/[a-zA-Z0-9_\\\\\\\\]+'.",
            "Element 'config_path': 'co' is " . "not a valid value of the atomic type 'typeConfigPath'."
        )
    ),
    'if_module_enabled_with_invalid_type' => array(
        '<?xml version="1.0"?><config><system><section id="section1"><group id="group1">' .
        '<label>Label</label><field id="field_id"><if_module_enabled>Som</if_module_enabled>' .
        '</field></group>' .
        '<group id="group2"><label>Label_One</label></group></section></system></config>',
        array(
            "Element 'if_module_enabled': [facet 'minLength'] The value has a length of '3'; this underruns the " .
            "allowed minimum length of '5'.",
            "Element 'if_module_enabled': [facet 'pattern'] The value 'Som' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}[_\\\\\\\\][A-Z]+[A-Z0-9a-z]{1,}'.",
            "Element 'if_module_enabled': 'Som' " . "is not a valid value of the atomic type 'typeModule'."
        )
    ),
    'id_minimum length' => array(
        '<?xml version="1.0"?><config><system><section id="s"><group id="gr">' .
        '<label>Label</label><field id="f"></field></group><group id="group1"><label>Label</label></group></section>' .
        '<tab id="h"><label>Label_One</label></tab></system></config>',
        array(
            "Element 'section', attribute 'id': [facet 'minLength'] The value 's' has a length of '1'; this " .
            "underruns the allowed minimum length of '2'.",
            "Element 'section', attribute 'id': 's' is not a valid value " . "of the atomic type 'typeId'.",
            "Element 'section', attribute 'id': Warning: No precomputed " .
            "value available, the value was either invalid or something strange happend.",
            "Element 'field', attribute " .
            "'id': [facet 'minLength'] The value 'f' has a length of '1'; this underruns the allowed minimum length " .
            "of '2'.",
            "Element 'field', attribute 'id': 'f' is not a valid value of the atomic type 'typeId'.",
            "Element" .
            " 'field', attribute 'id': ".
            "Warning: No precomputed value available, the value was either invalid or something" .
            " strange happend.",
            "Element 'tab', attribute 'id': [facet 'minLength'] The value 'h' has a length of '1'; " .
            "this underruns the allowed minimum length of '2'.",
            "Element 'tab', attribute 'id': 'h' is not a valid value" . " of the atomic type 'typeId'.",
            "Element 'tab', attribute 'id': Warning: No precomputed value available, " .
            "the value was either invalid or something strange happend."
        )
    ),
    'source_model_with_invalid_type' => array(
        '<?xml version="1.0"?><config><system><section id="section1"><group id="group1">' .
        '<label>Label</label><field id="field_id"><source_model>Sour</source_model>' .
        '</field></group>' .
        '<group id="group2"><label>Label_One</label></group></section></system></config>',
        array(
            "Element 'source_model': [facet 'minLength'] The value has a length of '4'; this underruns the allowed " .
            "minimum length of '5'.",
            "Element 'source_model': 'Sour' is not a valid value of the atomic" . " type 'typeModel'."
        )
    ),
    'base_url_with_invalid_type' => array(
        '<?xml version="1.0"?><config><system><section id="section1"><resource>One:</resource>' .
        '<group id="group1"><label>Label</label><field id="field_id"></field></group>' .
        '<group id="group2"><label>Label_One</label></group></section></system></config>',
        array(
            "Element 'resource': [facet 'minLength'] The value has a length of '4'; this underruns the allowed " .
            "minimum length of '8'.",
            "Element 'resource': [facet 'pattern'] The value 'One:' is not accepted by the " .
            "pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'resource': 'One:' is not " . "a valid value of the atomic type 'typeAclResourceId'."
        )
    ),
    'advanced_with_invalid_type' => array(
        '<?xml version="1.0"?><config><system><section id="section1" advanced="string">' .
        '<group id="group1"><label>Label</label><field id="field_id"></field></group>' .
        '<group id="group2"><label>Label_One</label></group></section></system></config>',
        array(
            "Element 'section', attribute 'advanced': 'string' is not a valid value of the atomic type " .
            "'xs:boolean'."
        )
    ),
    'advanced_attribute_with_invalid_value' => array(
        '<?xml version="1.0"?><config><system><section id="section1" advanced="string">' .
        '<group id="group1"><label>Label</label><field id="field_id" ></field></group>' .
        '<group id="group2"><label>Label_One</label></group></section></system></config>',
        array(
            "Element 'section', attribute 'advanced': 'string' is not a valid value of the atomic type " .
            "'xs:boolean'."
        )
    ),
    'options_node_without_any_options' => array(
        '<?xml version="1.0"?><config><system><section id="section1" advanced="false">' .
        '<group id="group1"><label>Label</label><field id="field_id"><options />' .
        '</field></group><group id="group2"><label>Label_One</label></group></section></system></config>',
        array("Element 'options': Missing child element(s). Expected is ( option ).")
    ),
    'system_node_without_allowed_elements' => array(
        '<?xml version="1.0"?><config><system/></config>',
        array("Element 'system': Missing child element(s). Expected is one of ( tab, section ).")
    ),
    'config_node_without_allowed_elements' => array(
        '<?xml version="1.0"?><config></config>',
        array("Element 'config': Missing child element(s). Expected is ( system ).")
    ),
    'config_without_required_attributes' => array(
        '<?xml version="1.0"?><config><system><section><group>' .
        '<label>Label</label><attribute/><field><depends><field/></depends><options><option/></options></field>' .
        '</group><group id="group2"><label>Label_One'.
        '</label></group></section><tab><label>Label</label></tab></system>' .
        '</config>',
        array(
            "Element 'section': The attribute 'id' is required but missing.",
            "Element 'group': The attribute 'id' " . "is required but missing.",
            "Element 'attribute': The attribute 'type' is " . "required but missing.",
            "Element 'field': The attribute 'id' is required but missing.",
            "Element " . "'field': The attribute 'id' is required but missing.",
            "Element 'option': The attribute 'label' is " . "required but missing.",
            "Element 'tab': The attribute 'id' is required but missing."
        )
    ),
    'attribute_type_is_unique' => array(
        '<?xml version="1.0"?><config><system><section id="name"><group id="name">' .
        '<label>Label</label><field id="name"><attribute type="one"/><attribute type="one"/></field>' .
        '</group><group id="group2"><label>Label_One</label></group></section></system>' .
        '</config>',
        array(
            "Element 'attribute': Duplicate key-sequence ['one'] in unique identity-constraint " .
            "'uniqueAttributeType'."
        )
    )
);
