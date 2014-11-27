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

namespace Magento\Wishlist\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Test Creation for ViewProductInCustomerWishlistOnBackend
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer.
 * 2. Create products from dataSet.
 * 3. Add products to the customer's wish list (composite products should be configured).
 *
 * Steps:
 * 1. Log in to backend.
 * 2. Go to Customers > All Customers.
 * 3. Search and open customer.
 * 4. Open wish list tab.
 * 5. Perform assertions.
 *
 * @group Wishlist_(CS)
 * @ZephyrId MAGETWO-29616
 */
class ViewProductInCustomerWishlistOnBackendTest extends AbstractWishlistTest
{
    /**
     * Prepare customer for test.
     *
     * @param CustomerInjectable $customer
     * @return array
     */
    public function __prepare(CustomerInjectable $customer)
    {
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Configure customer wish list on backend.
     *
     * @param CustomerInjectable $customer
     * @param string $product
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @return array
     */
    public function test(
        CustomerInjectable $customer,
        $product,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit
    ) {
        $this->markTestIncomplete('Bug: MAGETWO-27949');

        // Preconditions
        $product = $this->createProducts($product)[0];
        $this->loginCustomer($customer);
        $this->addToWishlist([$product], true);

        // Steps
        $customerIndex->open();
        $customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $customerIndexEdit->getCustomerForm()->openTab('wishlist');

        return['product' => $product];
    }
}
