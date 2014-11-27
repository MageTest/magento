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

namespace Magento\Customer\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Customer\Model\CustomerRegistry
 */
class CustomerRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $_model;

    /**#@+
     * Data set in customer fixture
     */
    const CUSTOMER_ID = 1;
    const CUSTOMER_EMAIL = 'customer@example.com';
    const WEBSITE_ID = 1;

    /**
     * Initialize SUT
     */
    protected function setUp()
    {
        $this->_model = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\CustomerRegistry');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testRetrieve()
    {
        $customer = $this->_model->retrieve(self::CUSTOMER_ID);
        $this->assertInstanceOf('\Magento\Customer\Model\Customer', $customer);
        $this->assertEquals(self::CUSTOMER_ID, $customer->getId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testRetrieveByEmail()
    {
        $customer = $this->_model->retrieveByEmail('customer@example.com', self::WEBSITE_ID);
        $this->assertInstanceOf('\Magento\Customer\Model\Customer', $customer);
        $this->assertEquals(self::CUSTOMER_EMAIL, $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppArea adminhtml
     */
    public function testRetrieveCached()
    {
        //Setup customer in the id and email registries
        $customerBeforeDeletion = $this->_model->retrieve(self::CUSTOMER_ID);
        //Delete the customer from db
        Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer')->load(self::CUSTOMER_ID)->delete();
        //Verify presence of Customer in registry
        $this->assertEquals($customerBeforeDeletion, $this->_model->retrieve(self::CUSTOMER_ID));
        //Verify presence of Customer in email registry
        $this->assertEquals($customerBeforeDeletion, $this->_model
                ->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId = 1
     */
    public function testRetrieveException()
    {
        $this->_model->retrieve(self::CUSTOMER_ID);
    }

    public function testRetrieveEmailException()
    {
        try {
            $this->_model->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
            $this->fail("NoSuchEntityException was not thrown as expected.");
        }  catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'email',
                'fieldValue' => 'customer@example.com',
                'field2Name' => 'websiteId',
                'field2Value' => 1,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @magentoAppArea adminhtml
     */
    public function testRemove()
    {
        $customer = $this->_model->retrieve(self::CUSTOMER_ID);
        $this->assertInstanceOf('\Magento\Customer\Model\Customer', $customer);
        $customer->delete();
        $this->_model->remove(self::CUSTOMER_ID);
        $this->_model->retrieve(self::CUSTOMER_ID);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @magentoAppArea adminhtml
     */
    public function testRemoveByEmail()
    {
        $customer = $this->_model->retrieve(self::CUSTOMER_ID);
        $this->assertInstanceOf('\Magento\Customer\Model\Customer', $customer);
        $customer->delete();
        $this->_model->removeByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->_model->retrieveByEmail(self::CUSTOMER_EMAIL, $customer->getWebsiteId());
    }
}
