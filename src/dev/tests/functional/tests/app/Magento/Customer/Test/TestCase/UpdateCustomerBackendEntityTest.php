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

namespace Magento\Customer\Test\TestCase;

use Mtf\TestCase\Injectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;

/**
 * Test Creation for UpdateCustomerBackendEntity
 *
 * General Flow:
 * 1. Login to backend as admin
 * 2. Navigate to CUSTOMERS->All Customers
 * 3. Open from grid test customer
 * 4. Edit some values, if addresses fields are not presented click 'Add New Address' button
 * 5. Click 'Save' button
 * 6. Perform all assertions
 *
 * @ticketId MAGETWO-23881
 */
class UpdateCustomerBackendEntityTest extends Injectable
{
    /**
     * @var CustomerIndex
     */
    protected $customerIndexPage;

    /**
     * @var CustomerIndexEdit
     */
    protected $customerIndexEditPage;

    /**
     * @param CustomerIndex $customerIndexPage
     * @param CustomerIndexEdit $customerIndexEditPage
     */
    public function __inject(
        CustomerIndex $customerIndexPage,
        CustomerIndexEdit $customerIndexEditPage
    ) {
        $this->customerIndexPage = $customerIndexPage;
        $this->customerIndexEditPage = $customerIndexEditPage;
    }

    /**
     * @param CustomerInjectable $initialCustomer
     * @param CustomerInjectable $customer
     * @param AddressInjectable $address
     */
    public function testUpdateCustomerBackendEntity(
        CustomerInjectable $initialCustomer,
        CustomerInjectable $customer,
        AddressInjectable $address
    ) {
        // Prepare data
        $address = $address->hasData() ? $address : null;

        // Preconditions:
        $initialCustomer->persist();

        // Steps
        $filter = ['email' => $initialCustomer->getEmail()];
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerIndexEditPage->getCustomerForm()->updateCustomer($customer, $address);
        $this->customerIndexEditPage->getPageActionsBlock()->save();
    }
}
