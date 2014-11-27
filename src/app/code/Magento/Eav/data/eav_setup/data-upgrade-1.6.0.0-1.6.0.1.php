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

/** @var $this \Magento\Eav\Model\Entity\Setup  */
$installer = $this->createMigrationSetup();
$installer->startSetup();

$installer->appendClassAliasReplace(
    'eav_attribute',
    'attribute_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('attribute_id')
);
$installer->appendClassAliasReplace(
    'eav_attribute',
    'backend_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('attribute_id')
);
$installer->appendClassAliasReplace(
    'eav_attribute',
    'frontend_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('attribute_id')
);
$installer->appendClassAliasReplace(
    'eav_attribute',
    'source_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('attribute_id')
);

$installer->appendClassAliasReplace(
    'eav_entity_type',
    'entity_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('entity_type_id')
);
$installer->appendClassAliasReplace(
    'eav_entity_type',
    'attribute_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('entity_type_id')
);
$installer->appendClassAliasReplace(
    'eav_entity_type',
    'increment_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('entity_type_id')
);
$installer->appendClassAliasReplace(
    'eav_entity_type',
    'entity_attribute_collection',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_RESOURCE,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('entity_type_id')
);

$installer->doUpdateClassAliases();

$installer->endSetup();
