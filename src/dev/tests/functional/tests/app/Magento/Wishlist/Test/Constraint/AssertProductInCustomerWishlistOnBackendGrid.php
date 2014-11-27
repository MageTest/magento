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

namespace Magento\Wishlist\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist\Grid;

/**
 * Class AssertProductInCustomerWishlistOnBackendGrid
 * Assert that product is present in grid on customer's wish list tab with configure option and qty
 */
class AssertProductInCustomerWishlistOnBackendGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that product is present in grid on customer's wish list tab with configure option and qty
     *
     * @param CustomerIndexEdit $customerIndexEdit
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CustomerIndexEdit $customerIndexEdit, FixtureInterface $product)
    {
        $filter = $this->prepareFilter($product);

        /** @var Grid $wishlistGrid */
        $wishlistGrid = $customerIndexEdit->getCustomerForm()->getTabElement('wishlist')->getSearchGridBlock();
        \PHPUnit_Framework_Assert::assertTrue(
            $wishlistGrid->isRowVisible($filter, true, false),
            'Product ' . $product->getName() . ' is absent in grid with configure option.'
        );
    }

    /**
     * Prepare filter
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareFilter(FixtureInterface $product)
    {
        $checkoutData = $product->getCheckoutData();
        $qty = isset($checkoutData['qty']) ? $checkoutData['qty'] : 1;
        $options = $this->prepareOptions($product);

        return ['product_name' => $product->getName(), 'qty_from' => $qty, 'qty_to' => $qty, 'options' => $options];
    }

    /**
     * Prepare options
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareOptions(FixtureInterface $product)
    {
        $productOptions = [];
        $checkoutData = $product->getCheckoutData()['options'];
        $customOptions = $product->getCustomOptions();
        if (isset($checkoutData['custom_options'])) {
            foreach ($checkoutData['custom_options'] as $option) {
                $optionKey = str_replace('attribute_key_', '', $option['title']);
                $valueKey = str_replace('option_key_', '', $option['value']);
                $productOptions[] = [
                    'option_name' => $customOptions[$optionKey]['title'],
                    'value' => isset($customOptions[$optionKey]['options'][$valueKey]['title'])
                        ? $customOptions[$optionKey]['options'][$valueKey]['title']
                        : $valueKey
                ];
            }
        }

        return $productOptions;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Product is visible in customer wishlist on backend.";
    }
}
