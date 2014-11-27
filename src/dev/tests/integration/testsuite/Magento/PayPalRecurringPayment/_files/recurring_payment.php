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

/** @var Magento\RecurringPayment\Model\Payment $recurringPayment */
$recurringPayment = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\RecurringPayment\Model\Payment'
);
$recurringPayment->addData(
    array(
        'store_id' => 1,
        'method_code' => \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS,
        'reference_id' => 'I-C76MC3FM2HBX',
        'internal_reference_id' => '5-33949e201adc4b03fbbceafccba893ce',
        'schedule_description' => 'Recurring Payment',
        'start_date_is_editable' => '0',
        'period_unit' => 'day',
        'period_frequency' => '1',
        'period_max_cycles' => '3',
        'trial_period_unit' => 'day',
        'trial_period_frequency' => '1',
        'trial_period_max_cycles' => '3',
        'trial_billing_amount' => '100.0000',
        'init_amount' => '100.0000',
        'billing_amount' => '100.0000',
        'currency_code' => 'USD',
        'order_info' => array('base_currency_code' => 'USD'),
        'order_item_info' => serialize('item info'),
        'billing_address_info' => serialize([
            'postcode' => '12345',
            'lastname' => 'Co',
            'street' => 'Street',
            'city' => 'City',
            'customer_email' => 'co@co.co',
            'telephone' => 'Phone Number',
            'country_id' => 'Country',
            'firstname' => 'Co',
            'address_type' => 'billing'
        ]),
        'shipping_address_info' => serialize([
            'postcode' => '12345',
            'lastname' => 'Co',
            'street' => 'Street',
            'city' => 'City',
            'customer_email' => 'co@co.co',
            'telephone' => 'Phone Number',
            'country_id' => 'Country',
            'firstname' => 'Co',
            'address_type' => 'shipping'
])
    )
);
$recurringPayment->save();
