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

namespace Magento\Sales\Block\Reorder;

use Magento\Customer\Model\Context;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoAppIsolation enabled
     */
    public function testInitOrdersCustomerWithOrder()
    {
        /** Preconditions */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $fixtureCustomerId = 1;
        $customerSession->setCustomerId($fixtureCustomerId);
        /** @var \Magento\Framework\App\Http\Context $httpContext */
        $httpContext = $objectManager->get('Magento\Framework\App\Http\Context');
        $httpContext->setValue(Context::CONTEXT_AUTH, true, false);

        /** Execute SUT implicitly: initOrders() is called in the construct */
        /** @var \Magento\Sales\Block\Reorder\Sidebar $sidebarBlock */
        $sidebarBlock = $objectManager->create('Magento\Sales\Block\Reorder\Sidebar');

        /** Ensure that customer orders were selected for the block */
        $customerOrders = $sidebarBlock->getOrders();
        $this->assertEquals(1, $customerOrders->count(), 'Invalid orders quantity.');
        $order = $customerOrders->getFirstItem();
        $this->assertEquals($fixtureCustomerId, $order->getCustomerId(), 'Customer ID in order is invalid.');
        $fixtureOrderIncrementId = '100000001';
        $this->assertEquals($fixtureOrderIncrementId, $order->getIncrementId(), 'Order increment ID is invalid.');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testInitOrdersCustomerWithoutOrders()
    {
        /** Preconditions */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $secondCustomer = $this->createSecondCustomer();
        $customerSession->setCustomerId($secondCustomer->getId());
        /** @var \Magento\Framework\App\Http\Context $httpContext */
        $httpContext = $objectManager->get('Magento\Framework\App\Http\Context');
        $httpContext->setValue(Context::CONTEXT_AUTH, true, false);

        /** Execute SUT implicitly: initOrders() is called in the construct */
        /** @var \Magento\Sales\Block\Reorder\Sidebar $sidebarBlock */
        $sidebarBlock = $objectManager->create('Magento\Sales\Block\Reorder\Sidebar');

        /** Ensure that customer orders were selected for the block */
        $customerOrders = $sidebarBlock->getOrders();
        $this->assertEquals(0, $customerOrders->count(), 'Filter by customer is applied to collection incorrectly.');
    }

    /*
     * Create customer which does not have any orders associated with him.
     *
     * Please note that tests which use this method must have DB isolation enabled.
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function createSecondCustomer()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $objectManager->create('Magento\Customer\Model\Customer');
        $customer->setWebsiteId(1)
            ->setEntityId(2)
            ->setEntityTypeId(1)
            ->setAttributeSetId(0)
            ->setEmail('customer2@search.example.com')
            ->setPassword('password')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setFirstname('Firstname2')
            ->setLastname('Lastname2')
            ->setDefaultBilling(2)
            ->setDefaultShipping(2)
            ->setCreatedAt('2010-02-28 15:52:26');
        $customer->isObjectNew(true);
        $customer->save();
        return $customer;
    }
}
