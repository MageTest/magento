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

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;

$productTypes = [
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
    \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
    \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
    \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
];
$productTypes = join(',', $productTypes);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'msrp',
    array(
        'group' => 'Advanced Pricing',
        'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
        'frontend' => '',
        'label' => 'Manufacturer\'s Suggested Retail Price',
        'type' => 'decimal',
        'input' => 'price',
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'apply_to' => $productTypes,
        'input_renderer' => 'Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type',
        'frontend_input_renderer' => 'Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type',
        'visible_on_front' => false,
        'used_in_product_listing' => true
    )
);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'msrp_display_actual_price_type',
    array(
        'group' => 'Advanced Pricing',
        'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Boolean',
        'frontend' => '',
        'label' => 'Display Actual Price',
        'input' => 'select',
        'source' => 'Magento\Msrp\Model\Product\Attribute\Source\Type\Price',
        'source_model' => 'Magento\Msrp\Model\Product\Attribute\Source\Type\Price',
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'default' => \Magento\Msrp\Model\Product\Attribute\Source\Type\Price::TYPE_USE_CONFIG,
        'default_value' => \Magento\Msrp\Model\Product\Attribute\Source\Type\Price::TYPE_USE_CONFIG,
        'apply_to' => $productTypes,
        'input_renderer' => 'Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type\Price',
        'frontend_input_renderer' => 'Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type\Price',
        'visible_on_front' => false,
        'used_in_product_listing' => true
    )
);
