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

namespace Magento\ConfigurableProduct\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Magento\Sales\Test\Constraint\AssertProductInItemsOrderedGrid;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;

/**
 * Class AssertConfigurableProductInItemsOrderedGrid
 * Assert configurable product was added to Items Ordered grid in customer account on Order creation page backend
 */
class AssertConfigurableProductInItemsOrderedGrid extends AssertProductInItemsOrderedGrid
{
    /**
     * Get configurable product price
     *
     * @param FixtureInterface $product
     * @throws \Exception
     * @return int
     */
    protected function getProductPrice(FixtureInterface $product)
    {
        $price = $product->getPrice();
        if (!$this->productsIsConfigured) {
            return $price;
        }
        if (!$product instanceof ConfigurableProductInjectable) {
            throw new \Exception("Product '$product->getName()' is not configurable product.");
        }
        $checkoutData = $product->getCheckoutData();
        if ($checkoutData === null) {
            return 0;
        }
        $attributesData = $product->getConfigurableAttributesData()['attributes_data'];
        foreach ($checkoutData['options']['configurable_options'] as $option) {
            $itemOption = $attributesData[$option['title']]['options'][$option['value']];
            $itemPrice = $itemOption['is_percent'] == 'No'
                ? $itemOption['pricing_value']
                : $product->getPrice() / 100 * $itemOption['pricing_value'];
            $price += $itemPrice;
        }

        return $price;
    }
}
