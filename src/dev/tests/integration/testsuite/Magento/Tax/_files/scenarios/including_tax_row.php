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

use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\SetupUtil;
use Magento\Tax\Model\Calculation;

$taxCalculationData['including_tax_row'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => [
            Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 1,
            Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX => 1,
            Config::XML_PATH_ALGORITHM => Calculation::CALC_ROW_BASE,
        ],
        SetupUtil::TAX_RATE_OVERRIDES => [
            SetupUtil::TAX_RATE_TX => 20,
            SetupUtil::TAX_STORE_RATE => 20,
        ],
        SetupUtil::TAX_RULE_OVERRIDES => [
        ],
    ],
    'quote_data' => [
        'billing_address' => [
            'region_id' => SetupUtil::REGION_TX,
        ],
        'shipping_address' => [
            'region_id' => SetupUtil::REGION_TX,
        ],
        'items' => [
            [
                'sku' => 'simple1',
                'price' => 9.99,
                'qty' => 2,
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 16.65,
            'base_subtotal' => 16.65,
            'subtotal_incl_tax' => 19.98,
            'base_subtotal_incl_tax' => 19.98,
            'tax_amount' => 3.33,
            'base_tax_amount' => 3.33,
            'shipping_amount' => 0,
            'base_shipping_amount' => 0,
            'shipping_incl_tax' => 0,
            'base_shipping_incl_tax' => 0,
            'shipping_taxable' => 0,
            'base_shipping_taxable' => 0,
            'shipping_tax_amount' => 0,
            'base_shipping_tax_amount' => 0,
            'discount_amount' => 0,
            'base_discount_amount' => 0,
            'hidden_tax_amount' => 0,
            'base_hidden_tax_amount' => 0,
            'shipping_hidden_tax_amount' => 0,
            'base_shipping_hidden_tax_amount' => 0,
            'grand_total' => 19.98,
            'base_grand_total' => 19.98,
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 16.65,
                'base_row_total' => 16.65,
                'tax_percent' => 20,
                'price' => 8.33,
                'base_price' => 8.33,
                'price_incl_tax' => 9.99,
                'base_price_incl_tax' => 9.99,
                'row_total_incl_tax' => 19.98,
                'base_row_total_incl_tax' => 19.98,
                'tax_amount' => 3.33,
                'base_tax_amount' => 3.33,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'hidden_tax_amount' => 0,
                'base_hidden_tax_amount' => 0,
            ],
        ],
    ],
];