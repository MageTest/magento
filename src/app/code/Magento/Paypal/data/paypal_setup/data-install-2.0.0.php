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

/** @var $installer \Magento\Sales\Model\Resource\Setup */
$installer = $this;

/**
 * Prepare database for install
 */
$installer->startSetup();
/**
 * Add paypal attributes to the:
 *  - sales/flat_quote_payment_item table
 *  - sales/flat_order table
 */
$installer->addAttribute('quote_payment', 'paypal_payer_id', array());
$installer->addAttribute('quote_payment', 'paypal_payer_status', array());
$installer->addAttribute('quote_payment', 'paypal_correlation_id', array());
$installer->addAttribute(
    'order',
    'paypal_ipn_customer_notified',
    array('type' => 'int', 'visible' => false, 'default' => 0)
);

$data = array();
$statuses = array(
    'pending_paypal' => __('Pending PayPal'),
    'paypal_reversed' => __('PayPal Reversed'),
    'paypal_canceled_reversal'  => __('PayPal Canceled Reversal')
);
foreach ($statuses as $code => $info) {
    $data[] = array('status' => $code, 'label' => $info);
}
$installer->getConnection()->insertArray($installer->getTable('sales_order_status'), array('status', 'label'), $data);

/**
 * Prepare database after install
 */
$installer->endSetup();
