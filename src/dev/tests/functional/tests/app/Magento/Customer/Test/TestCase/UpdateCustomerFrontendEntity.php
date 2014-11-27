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

use Magento\Customer\Test\Constraint\AssertCustomerInfoSuccessSavedMessage;
use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountEdit;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Page\CustomerAddressEdit;
use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Test Creation for UpdateCustomerFrontendEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Default test customer is created
 *
 * Steps:
 * 1. Login to fronted as test customer from preconditions
 * 2. Navigate to Account Dashboard page:
 * 3. Click "Edit" link near "Contact Information"
 * 4. Fill fields with test data and save
 * 5. Click "Edit Address" link near "Default Billing Address", save and return to Account Dashboard page
 * 6. Fill fields with test data and save
 * 7. Perform all assertions
 *
 * @group Customer_Account_(CS)
 * @ZephyrId MAGETWO-25925
 */
class UpdateCustomerFrontendEntity extends Injectable
{
    /**
     * Factory for Fixtures
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * CmsIndex page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * CustomerAccountLogin page
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * CustomerAccountIndex page
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccountIndex;

    /**
     * CustomerAccountEdit page
     *
     * @var CustomerAccountEdit
     */
    protected $customerAccountEdit;

    /**
     * CustomerAddressEdit page
     *
     * @var CustomerAddressEdit
     */
    protected $customerAddressEdit;

    /**
     * Preparing data for test
     *
     * @param CmsIndex $cmsIndex
     * @param FixtureFactory $fixtureFactory
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerAccountEdit $customerAccountEdit
     * @param CustomerAddressEdit $customerAddressEdit
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        FixtureFactory $fixtureFactory,
        CustomerAccountLogin $customerAccountLogin,
        CustomerAccountIndex $customerAccountIndex,
        CustomerAccountEdit $customerAccountEdit,
        CustomerAddressEdit $customerAddressEdit
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->fixtureFactory = $fixtureFactory;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customerAccountIndex = $customerAccountIndex;
        $this->customerAccountEdit = $customerAccountEdit;
        $this->customerAddressEdit = $customerAddressEdit;
    }

    /**
     * Run Update Customer Entity test
     *
     * @param CustomerInjectable $initialCustomer
     * @param CustomerInjectable $customer
     * @param AddressInjectable $address
     * @param AssertCustomerInfoSuccessSavedMessage $assertCustomerInfoSuccessSavedMessage
     * @return void
     */
    public function test(
        CustomerInjectable $initialCustomer,
        CustomerInjectable $customer,
        AddressInjectable $address,
        AssertCustomerInfoSuccessSavedMessage $assertCustomerInfoSuccessSavedMessage
    ) {
        // Preconditions
        $initialCustomer->persist();

        // Steps
        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink('Log In');
        $this->customerAccountLogin->getLoginBlock()->fill($initialCustomer);
        $this->customerAccountLogin->getLoginBlock()->submit();

        $this->customerAccountIndex->getInfoBlock()->openEditContactInfo();
        $this->customerAccountEdit->getAccountInfoForm()->fill($customer);
        $this->customerAccountEdit->getAccountInfoForm()->submit();

        \PHPUnit_Framework_Assert::assertThat($this->getName(), $assertCustomerInfoSuccessSavedMessage);

        $this->customerAccountIndex->getDashboardAddress()->editBillingAddress();
        $this->customerAddressEdit->getEditForm()->fill($address);
        $this->customerAddressEdit->getEditForm()->saveAddress();
    }

    /**
     * Customer logout from account
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->cmsIndex->getLinksBlock()->isVisible()) {
            $this->cmsIndex->getLinksBlock()->openLink('Log Out');
        }
    }
}
