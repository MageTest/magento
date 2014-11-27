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


/** @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'gift_message'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('gift_message')
)->addColumn(
    'gift_message_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'GiftMessage Id'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Customer id'
)->addColumn(
    'sender',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Sender'
)->addColumn(
    'recipient',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Recipient'
)->addColumn(
    'message',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    null,
    array(),
    'Message'
)->setComment(
    'Gift Message'
);

$installer->getConnection()->createTable($table);

$installer->endSetup();
