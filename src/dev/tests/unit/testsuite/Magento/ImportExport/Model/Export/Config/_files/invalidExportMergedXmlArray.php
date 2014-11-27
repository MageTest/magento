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
    'fileFormat_node_with_required_attribute' => array(
        '<?xml version="1.0"?><config><fileFormat label="name_one" model="model"/><fileFormat name="name_one" ' .
        'model="model"/><fileFormat name="name" label="model"/></config>',
        array(
            "Element 'fileFormat': The attribute 'name' is required but missing.",
            "Element 'fileFormat': The " . "attribute 'label' is required but missing.",
            "Element 'fileFormat': The attribute 'model' is required but " . "missing."
        )
    ),
    'entity_node_with_required_attribute' => array(
        '<?xml version="1.0"?><config><entity label="name_one" model="model" entityAttributeFilterType="name_one"/>' .
        '<entity name="name_one" model="model" entityAttributeFilterType="name_two"/>' .
        '<entity label="name" name="model" entityAttributeFilterType="name_three"/>' .
        '<entity label="name" name="model_two" model="model"/></config>',
        array(
            "Element 'entity': The attribute 'name' is required but missing.",
            "Element 'entity': The attribute " . "'label' is required but missing.",
            "Element 'entity': The attribute 'model' is required but missing.",
            "Element 'entity': The attribute 'entityAttributeFilterType' is required but missing."
        )
    )
);
